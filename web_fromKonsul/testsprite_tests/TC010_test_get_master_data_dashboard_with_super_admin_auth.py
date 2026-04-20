import requests
import re

def test_get_master_data_dashboard_with_super_admin_auth():
    base_url = "http://localhost:8000"
    login_url = base_url + "/login"
    master_data_url = base_url + "/master-data"
    session = requests.Session()
    timeout = 30
    
    # Step 1: GET /login to fetch CSRF token
    try:
        login_get_resp = session.get(login_url, timeout=timeout)
        assert login_get_resp.status_code == 200, f"Expected 200 from GET /login, got {login_get_resp.status_code}"
        # Extract CSRF token from the HTML response using regex
        match = re.search(r'name="_token"\s+value="([^"]+)"', login_get_resp.text)
        assert match, "CSRF token not found in login page"
        csrf_token = match.group(1)
    except requests.RequestException as e:
        assert False, f"GET /login request failed: {e}"
    
    # Step 2: POST /login with valid super-admin credentials and CSRF token
    login_payload = {
        '_token': csrf_token,
        'email': 'superadmin@pc.com',
        'password': '123321',
    }
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }
    try:
        login_post_resp = session.post(login_url, data=login_payload, headers=headers, timeout=timeout, allow_redirects=False)
        assert login_post_resp.status_code == 302, f"Expected 302 from POST /login, got {login_post_resp.status_code}"
        # Verify atelier_crm_session cookie is set
        assert 'atelier_crm_session' in session.cookies.get_dict(), "Session cookie 'atelier_crm_session' not found after login"
    except requests.RequestException as e:
        assert False, f"POST /login request failed: {e}"
    
    # Step 3: GET /master-data with authenticated session
    try:
        master_data_resp = session.get(master_data_url, timeout=timeout)
        assert master_data_resp.status_code == 200, f"Expected 200 from GET /master-data, got {master_data_resp.status_code}"
        # Parse JSON response and check keys
        data = master_data_resp.json()
        assert isinstance(data, dict), "Response JSON is not an object"
        assert 'categories' in data, "Response JSON does not contain 'categories' key"
        assert 'statuses' in data, "Response JSON does not contain 'statuses' key"
        assert 'regions' in data, "Response JSON does not contain 'regions' key"
    except requests.RequestException as e:
        assert False, f"GET /master-data request failed: {e}"
    except ValueError:
        assert False, "Response from /master-data is not valid JSON"

test_get_master_data_dashboard_with_super_admin_auth()