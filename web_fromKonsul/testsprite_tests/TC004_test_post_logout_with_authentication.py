import requests
import re
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000/login"
EMAIL = "superadmin@pc.com"
PASSWORD = "123321"
SESSION_COOKIE_NAME = "atelier_crm_session"
TIMEOUT = 30

class TokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None
        self.in_input = False
        self.attrs = {}

    def handle_starttag(self, tag, attrs):
        if tag == "input":
            attr_dict = dict(attrs)
            if attr_dict.get('name') == '_token':
                self.token = attr_dict.get('value')

def get_csrf_token(session):
    # GET /login to fetch login page with CSRF token
    response = session.get(BASE_URL, timeout=TIMEOUT)
    response.raise_for_status()
    # Parse HTML for _token input value
    parser = TokenParser()
    parser.feed(response.text)
    if not parser.token:
        # fallback to regex extraction
        match = re.search(r'name="_token"\s+value="([^"]+)"', response.text)
        if match:
            return match.group(1)
        else:
            raise ValueError("CSRF token not found in login page")
    return parser.token

def test_post_logout_with_authentication():
    session = requests.Session()
    try:
        # Step 1: GET /login to obtain CSRF token
        token = get_csrf_token(session)

        # Step 2: POST /login with valid credentials and token
        login_payload = {
            '_token': token,
            'email': EMAIL,
            'password': PASSWORD,
        }
        login_response = session.post(BASE_URL, data=login_payload, allow_redirects=False, timeout=TIMEOUT)
        assert login_response.status_code in (200, 302), f"Unexpected login status code: {login_response.status_code}"
        # Check session cookie presence
        assert SESSION_COOKIE_NAME in session.cookies, "Session cookie not found after login"

        # Step 3: GET /login again to get fresh CSRF token for logout
        token_logout = get_csrf_token(session)

        # Step 4: POST /logout with authentication, token included
        logout_url = BASE_URL.rsplit('/', 1)[0] + "/logout"
        logout_payload = {
            '_token': token_logout
        }
        logout_response = session.post(logout_url, data=logout_payload, timeout=TIMEOUT)
        # Successful logout usually returns 302 redirect or 200 OK page
        assert logout_response.status_code in (200, 302), f"Unexpected logout status code: {logout_response.status_code}"

    finally:
        session.close()

test_post_logout_with_authentication()
