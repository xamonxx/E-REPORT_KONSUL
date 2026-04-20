import requests
import re
from html.parser import HTMLParser
from io import BytesIO

BASE_URL = "http://localhost:8000"

class TokenParser(HTMLParser):
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

def get_csrf_token(session):
    resp = session.get(f"{BASE_URL}/login", timeout=30)
    resp.raise_for_status()
    parser = TokenParser()
    parser.feed(resp.text)
    if not parser.token:
        raise ValueError("CSRF token not found in login page")
    return parser.token

def login(session, email, password):
    token = get_csrf_token(session)
    login_data = {
        "email": email,
        "password": password,
        "_token": token
    }
    resp = session.post(f"{BASE_URL}/login", data=login_data, timeout=30, allow_redirects=False)
    assert resp.status_code in (200, 302), f"Unexpected login status code: {resp.status_code}"
    assert "atelier_crm_session" in resp.cookies, "Session cookie 'atelier_crm_session' not found after login"
    return resp

def test_post_import_consultations_via_csv():
    session = requests.Session()
    try:
        # Login first
        login(session, "superadmin@pc.com", "123321")

        # Get CSRF token for import
        token = get_csrf_token(session)

        # Prepare CSV content for import
        csv_content = (
            "name,email,phone\n"
            "John Doe,john@example.com,1234567890\n"
            "Jane Smith,jane@example.com,0987654321\n"
        ).encode("utf-8")

        files = {
            "csv": ("consultations_import.csv", BytesIO(csv_content), "text/csv")
        }
        data = {
            "_token": token
        }

        resp = session.post(f"{BASE_URL}/consultations/import", data=data, files=files, timeout=30)
        assert resp.status_code == 200, f"Expected 200 status code, got {resp.status_code}"
        assert 'application/json' in resp.headers.get('Content-Type', ''), "Response content-type is not JSON"
        json_resp = resp.json()
        # Check import summary fields
        assert "rows_processed" in json_resp, "'rows_processed' key missing in response"
        assert "errors" in json_resp, "'errors' key missing in response"
        # Expect no errors in a valid CSV import
        assert isinstance(json_resp["errors"], list), "'errors' should be a list"
        assert len(json_resp["errors"]) == 0, f"Expected zero import errors but found: {json_resp['errors']}"

        # Verify imported consultations appear in the list
        list_resp = session.get(f"{BASE_URL}/consultations", timeout=30)
        assert list_resp.status_code == 200, f"Expected 200 status code from consultations list, got {list_resp.status_code}"
        consultations = list_resp.json()
        # Expect at least the imported names present
        names_in_list = [c.get("name") for c in consultations if c.get("name")]
        assert "John Doe" in names_in_list, "Imported consultation 'John Doe' not found in consultations list"
        assert "Jane Smith" in names_in_list, "Imported consultation 'Jane Smith' not found in consultations list"

    finally:
        # Cleanup: no direct deletion code as import is bulk and no IDs returned; test environment cleanup should handle this.
        session.close()

test_post_import_consultations_via_csv()
