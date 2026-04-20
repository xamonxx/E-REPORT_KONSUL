import requests
import re
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000/login"
TIMEOUT = 30

class CSRFTokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None

    def handle_starttag(self, tag, attrs):
        if tag == 'input':
            attr_dict = dict(attrs)
            if attr_dict.get('name') == '_token':
                self.token = attr_dict.get('value')

def test_get_login_page():
    try:
        # GET /login to get the login page with CSRF token
        response = requests.get(BASE_URL, timeout=TIMEOUT)
        assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"

        # Parse CSRF token from response HTML
        parser = CSRFTokenParser()
        parser.feed(response.text)
        token = parser.token
        assert token is not None, "CSRF token '_token' not found in login page HTML"

        # For completeness, check the token field presence by regex as backup
        match = re.search(r'<input[^>]+name=["\']_token["\'][^>]*value=["\']([^"\']+)["\']', response.text)
        assert match is not None, "CSRF token input field not found by regex"
        assert match.group(1) == token, "CSRF token mismatch between parser and regex"

    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

test_get_login_page()