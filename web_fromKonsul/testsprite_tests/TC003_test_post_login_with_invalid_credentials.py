import requests
import re
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000/login"
TIMEOUT = 30

class TokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.csrf_token = None

    def handle_starttag(self, tag, attrs):
        if tag == "input":
            attr_dict = dict(attrs)
            if attr_dict.get("name") == "_token":
                self.csrf_token = attr_dict.get("value")

def test_post_login_with_invalid_credentials():
    session = requests.Session()
    try:
        # Step 1: GET /login to retrieve CSRF token
        response = session.get(BASE_URL, timeout=TIMEOUT)
        assert response.status_code == 200, f"Expected 200 OK from GET /login, got {response.status_code}"
        parser = TokenParser()
        parser.feed(response.text)
        csrf_token = parser.csrf_token
        assert csrf_token is not None, "CSRF token not found in login page"

        # Step 2: POST /login with invalid credentials and CSRF token
        payload = {
            "_token": csrf_token,
            "email": "invalid@example.com",
            "password": "wrongpassword"
        }
        post_resp = session.post(BASE_URL, data=payload, timeout=TIMEOUT)
        # The test expects 401 Unauthorized on invalid credentials
        assert post_resp.status_code == 401, f"Expected 401 Unauthorized for invalid login, got {post_resp.status_code}"
        # Session cookie should NOT be present on failed login
        assert 'atelier_crm_session' not in post_resp.cookies, "Session cookie should not be set on invalid login"
    finally:
        session.close()

test_post_login_with_invalid_credentials()