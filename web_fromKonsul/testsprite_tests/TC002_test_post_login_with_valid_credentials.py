import requests
import re

def test_post_login_with_valid_credentials():
    base_url = "http://localhost:8000"
    session = requests.Session()
    try:
        # Step 1: GET /login to fetch CSRF _token
        response_get = session.get(f"{base_url}/login", timeout=30)
        assert response_get.status_code == 200, f"Expected 200 on GET /login but got {response_get.status_code}"
        
        # Extract CSRF token from HTML input named '_token'
        token_match = re.search(r'name="_token"\s+value="([^"]+)"', response_get.text)
        assert token_match, "CSRF token '_token' not found in login page"
        csrf_token = token_match.group(1)
        
        # Step 2: POST /login with valid credentials and _token
        payload = {
            '_token': csrf_token,
            'email': 'superadmin@pc.com',
            'password': '123321'
        }
        response_post = session.post(f"{base_url}/login", data=payload, allow_redirects=False, timeout=30)
        
        # Assert the status code is either 200 or 302
        assert response_post.status_code in (200, 302), f"Expected status code 200 or 302, got {response_post.status_code}"
        
        # Assert the session cookie named 'atelier_crm_session' is set in the response cookies
        cookies_set = response_post.cookies or session.cookies
        session_cookie = None
        for cookie in cookies_set:
            if cookie.name == 'atelier_crm_session':
                session_cookie = cookie.value
                break
        assert session_cookie, "Session cookie 'atelier_crm_session' not found after login"
        
    finally:
        session.close()

test_post_login_with_valid_credentials()