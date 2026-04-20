import requests
import re
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000"
LOGIN_URL = BASE_URL + "/login"
CONSULTATIONS_URL = BASE_URL + "/consultations"
SESSION_COOKIE_NAME = "atelier_crm_session"
TIMEOUT = 30
EMAIL = "superadmin@pc.com"
PASSWORD = "123321"


class TokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None

    def handle_starttag(self, tag, attrs):
        if tag == "input":
            attr_dict = dict(attrs)
            if attr_dict.get("name") == "_token":
                self.token = attr_dict.get("value")


def get_csrf_token(session):
    resp = session.get(LOGIN_URL, timeout=TIMEOUT)
    resp.raise_for_status()
    parser = TokenParser()
    parser.feed(resp.text)
    if not parser.token:
        raise ValueError("CSRF token not found on login page")
    return parser.token


def login(session):
    token = get_csrf_token(session)
    payload = {
        "_token": token,
        "email": EMAIL,
        "password": PASSWORD,
    }
    resp = session.post(LOGIN_URL, data=payload, allow_redirects=False, timeout=TIMEOUT)
    assert resp.status_code in (200, 302), f"Unexpected login status code {resp.status_code}"
    cookies = session.cookies.get_dict()
    assert SESSION_COOKIE_NAME in cookies, "Session cookie not found after login"


def create_consultation(session, token):
    payload = {
        "_token": token,
        "name": "Test Consultation",
        "email": "test_consultation@example.com",
        "phone": "1234567890",
        "status_id": 1,
        "source": "Test Source",
        "account_id": 1
    }
    resp = session.post(CONSULTATIONS_URL, data=payload, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code in (200, 201), f"Unexpected status code when creating consultation: {resp.status_code}"
    resp_json = resp.json()
    assert "id" in resp_json, "Created consultation ID missing in response"
    return resp_json["id"]


def delete_consultation(session, consultation_id, token):
    url = f"{CONSULTATIONS_URL}/{consultation_id}"
    payload = {"_token": token}
    resp = session.delete(url, data=payload, timeout=TIMEOUT)
    return resp


def test_delete_consultation_with_authentication():
    session = requests.Session()
    try:
        # Login
        login(session)

        # Get CSRF token for subsequent requests
        token = get_csrf_token(session)

        # Create a consultation to delete
        consultation_id = create_consultation(session, token)

        # Delete the consultation
        resp = delete_consultation(session, consultation_id, token)
        assert resp.status_code == 204, f"Expected 204 on delete, got {resp.status_code}"

        # Verify consultation is deleted by getting list and ensuring id not present
        list_resp = session.get(CONSULTATIONS_URL, timeout=TIMEOUT)
        assert list_resp.status_code == 200, f"Failed to list consultations after delete, status {list_resp.status_code}"
        consultations = list_resp.json()
        # consultations expected to be a list of dicts containing 'id' keys
        ids = [c.get("id") for c in consultations if isinstance(c, dict)]
        assert consultation_id not in ids, "Deleted consultation still present in consultations list"

    finally:
        # Cleanup: try to delete consultation if still exists
        try:
            token = get_csrf_token(session)
            delete_consultation(session, consultation_id, token)
        except Exception:
            pass


test_delete_consultation_with_authentication()
