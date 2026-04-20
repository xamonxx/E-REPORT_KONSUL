import requests
import re

session = requests.Session()
r1 = session.get('http://localhost:8000/login')
print("Login GET status:", r1.status_code)
match = re.search(r'<input[^>]+name="_token"[^>]+value="([^"]+)"', r1.text)
if not match:
    print("Could not find token. Text excerpt:", r1.text[:500])
    exit(1)
token = match.group(1)
print("Token:", token)

payload = {
    '_token': token,
    'email': 'superadmin@pc.com',
    'password': '123321'
}

r2 = session.post('http://localhost:8000/login', data=payload, allow_redirects=False)
print("POST status:", r2.status_code)
print("Location:", r2.headers.get('Location'))
print("Cookies:", r2.cookies.get_dict())
print("Text sample:", r2.text[:200])
