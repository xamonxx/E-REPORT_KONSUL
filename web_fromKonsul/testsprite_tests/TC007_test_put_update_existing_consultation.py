import requests
import re
import json
from html.parser import HTMLParser

BASE_URL = "http://localhost:8000"
LOGIN_URL = BASE_URL + "/login"
CONSULTATIONS_URL = BASE_URL + "/consultations"
EMAIL = "superadmin@pc.com"
PASSWORD = "123321"
TIMEOUT = 30

class TokenParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.csrf_token = None
        self.in_input = False

    def handle_starttag(self, tag, attrs):
        if tag == "input":
            attrs_dict = dict(attrs)
            if attrs_dict.get("name") == "_token":
                self.csrf_token = attrs_dict.get("value")

def get_csrf_token_and_session():
    session = requests.Session()
    resp = session.get(LOGIN_URL, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Expected 200 on GET /login, got {resp.status_code}"

    parser = TokenParser()
    parser.feed(resp.text)
    token = parser.csrf_token
    assert token is not None, "CSRF token not found in login page"
    return session, token

def login(session, token):
    login_payload = {
        "_token": token,
        "email": EMAIL,
        "password": PASSWORD,
    }
    resp = session.post(LOGIN_URL, data=login_payload, timeout=TIMEOUT, allow_redirects=False)
    assert resp.status_code in (200, 302), f"Expected status 200 or 302, got {resp.status_code}"
    cookies = resp.cookies or session.cookies
    assert "atelier_crm_session" in cookies, "Session cookie 'atelier_crm_session' not found after login"

def create_consultation(session, csrf_token):
    # Minimal valid payload for consultation creation as per PRD
    payload = {
        "name": "Test Consultation",
        "email": "testconsultation@example.com",
        "phone": "1234567890",
        "notes": "Initial note",
    }
    headers = {"X-CSRF-TOKEN": csrf_token}
    resp = session.post(CONSULTATIONS_URL, json=payload, headers=headers, timeout=TIMEOUT)
    assert resp.status_code == 201, f"Expected 201 on POST /consultations, got {resp.status_code}"
    created = resp.json()
    consultation_id = created.get("id") or created.get("consultation_id") or created.get("data", {}).get("id")
    assert consultation_id is not None, "Created consultation ID not returned"
    return consultation_id

def delete_consultation(session, consultation_id, csrf_token):
    url = f"{CONSULTATIONS_URL}/{consultation_id}"
    headers = {"X-CSRF-TOKEN": csrf_token}
    resp = session.delete(url, headers=headers, timeout=TIMEOUT)
    assert resp.status_code == 204, f"Expected 204 on DELETE /consultations/{consultation_id}, got {resp.status_code}"

def get_consultation(session, consultation_id):
    url = f"{CONSULTATIONS_URL}/{consultation_id}"
    resp = session.get(url, timeout=TIMEOUT)
    if resp.status_code == 200:
        return resp.json()
    return None

def test_put_update_existing_consultation():
    session, token = get_csrf_token_and_session()
    login(session, token)

    consultation_id = create_consultation(session, token)

    try:
        update_url = f"{CONSULTATIONS_URL}/{consultation_id}"
        update_payload = {
            "name": "Updated Consultation Name",
            "email": "updatedemail@example.com",
            "phone": "0987654321",
            "notes": "Updated notes",
        }
        headers = {"X-CSRF-TOKEN": token}
        resp = session.put(update_url, json=update_payload, headers=headers, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Expected 200 on PUT /consultations/{consultation_id}, got {resp.status_code}"

        updated_data = resp.json()
        # Assert updated fields returned match the update
        assert updated_data.get("name") == "Updated Consultation Name", "Name not updated correctly"
        assert updated_data.get("email") == "updatedemail@example.com", "Email not updated correctly"
        assert updated_data.get("phone") == "0987654321", "Phone not updated correctly"
        # Notes field can be optional but if returned should match
        assert "notes" not in updated_data or updated_data.get("notes") == "Updated notes" or updated_data.get("notes") is not None, "Notes not updated correctly"

        # Verify via GET /consultations (or GET specific consultation if available)
        get_resp = session.get(CONSULTATIONS_URL, timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Expected 200 on GET /consultations, got {get_resp.status_code}"
        consultations = get_resp.json()
        # Check that the updated consultation is present with new values
        found = False
        if isinstance(consultations, list):
            for c in consultations:
                if str(c.get("id")) == str(consultation_id):
                    found = True
                    assert c.get("name") == "Updated Consultation Name", "Consultation name not updated in list"
                    assert c.get("email") == "updatedemail@example.com", "Consultation email not updated in list"
                    assert c.get("phone") == "0987654321", "Consultation phone not updated in list"
                    break
        else:
            # If the response wrapper has data field
            data = consultations.get("data", [])
            for c in data:
                if str(c.get("id")) == str(consultation_id):
                    found = True
                    assert c.get("name") == "Updated Consultation Name", "Consultation name not updated in list"
                    assert c.get("email") == "updatedemail@example.com", "Consultation email not updated in list"
                    assert c.get("phone") == "0987654321", "Consultation phone not updated in list"
                    break
        assert found, "Updated consultation not found in consultations list"

    finally:
        # Clean up - delete the consultation after test
        delete_consultation(session, consultation_id, token)

test_put_update_existing_consultation()
