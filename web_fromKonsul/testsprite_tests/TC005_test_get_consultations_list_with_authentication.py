import re
import requests
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000"
LOGIN_URL = f"{BASE_URL}/login"
CONSULTATIONS_URL = f"{BASE_URL}/consultations"
TIMEOUT = 30

class CsrfTokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None
        self.in_input = False
        self.attrs = {}

    def handle_starttag(self, tag, attrs):
        if tag == "input":
            attr_dict = dict(attrs)
            if attr_dict.get("name") == "_token":
                self.token = attr_dict.get("value")

def extract_csrf_token(html_text):
    parser = CsrfTokenParser()
    parser.feed(html_text)
    if not parser.token:
        # fallback regex if parser doesn't find token
        match = re.search(r'name="_token"\s+value="([^"]+)"', html_text)
        if match:
            return match.group(1)
        return None
    return parser.token

def test_get_consultations_list_with_authentication():
    session = requests.Session()
    # Step 1: GET /login to retrieve CSRF token
    resp = session.get(LOGIN_URL, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Expected 200 from GET /login but got {resp.status_code}"
    csrf_token = extract_csrf_token(resp.text)
    assert csrf_token, "CSRF token not found in login page"

    # Step 2: POST /login with valid credentials and CSRF token
    login_payload = {
        "_token": csrf_token,
        "email": "superadmin@pc.com",
        "password": "123321"
    }
    resp = session.post(LOGIN_URL, data=login_payload, timeout=TIMEOUT, allow_redirects=True)
    assert resp.status_code in (200, 302), f"Expected 200 or 302 from POST /login but got {resp.status_code}"
    # Check that at least one cookie is set
    cookies = session.cookies.get_dict()
    assert cookies, "Session cookies not found after login"

    # Step 3: GET /consultations with session cookie established
    resp = session.get(CONSULTATIONS_URL, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Expected 200 from GET /consultations but got {resp.status_code}"
    content_type = resp.headers.get('Content-Type', '')
    assert 'application/json' in content_type, f"Expected JSON Content-Type but got {content_type}"
    try:
        consultations_json = resp.json()
    except Exception as e:
        raise AssertionError(f"Response from GET /consultations is not valid JSON: {e}")
    assert isinstance(consultations_json, list), f"Expected a list of consultations but got {type(consultations_json)}"

test_get_consultations_list_with_authentication()
