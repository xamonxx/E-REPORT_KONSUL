import re
import requests
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000"
LOGIN_URL = BASE_URL + "/login"
CONSULTATIONS_URL = BASE_URL + "/consultations"
TIMEOUT = 30

EMAIL = "superadmin@pc.com"
PASSWORD = "123321"
SESSION_COOKIE_NAME = "atelier_crm_session"


class CsrfTokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None

    def handle_starttag(self, tag, attrs):
        if tag == "input":
            attr_dict = dict(attrs)
            if attr_dict.get("name") == "_token":
                self.token = attr_dict.get("value")


def test_post_create_consultation_with_valid_data():
    session = requests.Session()
    # Step 1: GET /login to fetch CSRF token
    resp_get_login = session.get(LOGIN_URL, timeout=TIMEOUT)
    assert resp_get_login.status_code == 200, f"Expected 200 on GET /login but got {resp_get_login.status_code}"
    parser = CsrfTokenParser()
    parser.feed(resp_get_login.text)
    csrf_token = parser.token
    assert csrf_token, "CSRF token not found in login page HTML"

    # Step 2: POST /login with credentials and CSRF token
    login_payload = {
        "_token": csrf_token,
        "email": EMAIL,
        "password": PASSWORD
    }
    resp_post_login = session.post(LOGIN_URL, data=login_payload, timeout=TIMEOUT)
    assert resp_post_login.status_code in (200, 302), f"Expected 200 or 302 on POST /login but got {resp_post_login.status_code}"
    assert SESSION_COOKIE_NAME in session.cookies, f"Session cookie '{SESSION_COOKIE_NAME}' not found after login"

    # Step 3: GET /consultations to get CSRF token for POST
    resp_get_consultations = session.get(CONSULTATIONS_URL, timeout=TIMEOUT)
    assert resp_get_consultations.status_code == 200, f"Expected 200 on GET /consultations but got {resp_get_consultations.status_code}"

    # Get fresh CSRF token for POST
    resp_get_login2 = session.get(LOGIN_URL, timeout=TIMEOUT)
    assert resp_get_login2.status_code == 200, f"Expected 200 on second GET /login but got {resp_get_login2.status_code}"
    parser2 = CsrfTokenParser()
    parser2.feed(resp_get_login2.text)
    csrf_token_post = parser2.token
    assert csrf_token_post, "CSRF token not found for POST /consultations"

    # Step 4: POST /consultations with minimal valid data and CSRF token
    payload = {
        "_token": csrf_token_post,
        "name": "Test Consultation",
        "email": "testconsultation@example.com",
        "phone": "1234567890"
    }

    try:
        resp_post_consultation = session.post(CONSULTATIONS_URL, data=payload, timeout=TIMEOUT)
        assert resp_post_consultation.status_code in (200, 201), f"Expected 200 or 201 on POST /consultations but got {resp_post_consultation.status_code}"

        # Check content-type before json parsing
        content_type = resp_post_consultation.headers.get('Content-Type', '')
        assert 'application/json' in content_type, f"Response content-type is not application/json but {content_type}"

        json_resp = resp_post_consultation.json()
        assert "id" in json_resp, "Response JSON does not contain consultation ID"
        consultation_id = json_resp["id"]

        # Verify consultation_id type
        assert isinstance(consultation_id, int) or (isinstance(consultation_id, str) and consultation_id.isdigit()), "Consultation ID is not valid"

    finally:
        # Cleanup: Delete the consultation created if consultation_id set
        if 'consultation_id' in locals():
            resp_get_login_del = session.get(LOGIN_URL, timeout=TIMEOUT)
            assert resp_get_login_del.status_code == 200, f"Expected 200 on GET /login before DELETE but got {resp_get_login_del.status_code}"
            parser_del = CsrfTokenParser()
            parser_del.feed(resp_get_login_del.text)
            csrf_token_del = parser_del.token
            if csrf_token_del:
                headers = {"X-Requested-With": "XMLHttpRequest"}
                delete_payload = {"_token": csrf_token_del}
                resp_delete = session.delete(f"{CONSULTATIONS_URL}/{consultation_id}", data=delete_payload, headers=headers, timeout=TIMEOUT)
                assert resp_delete.status_code in (200, 204, 404), f"Unexpected status code on DELETE /consultations/{consultation_id}: {resp_delete.status_code}"


test_post_create_consultation_with_valid_data()
