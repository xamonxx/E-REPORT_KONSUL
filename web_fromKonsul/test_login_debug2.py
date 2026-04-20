import urllib.request
import urllib.parse
import re

# GET to /login
req1 = urllib.request.Request('http://localhost:8000/login', method='GET')
try:
    with urllib.request.urlopen(req1) as response1:
        html = response1.read().decode('utf-8')
        cookie = response1.headers.get('Set-Cookie')
        print("GET status:", response1.status)
        match = re.search(r'<input[^>]+name="_token"[^>]+value="([^"]+)"', html)
        if not match:
            print("Token not found")
            exit(1)
        token = match.group(1)
        print("Token:", token)
except Exception as e:
    print("GET error:", e)
    exit(1)

# POST to /login
data = urllib.parse.urlencode({
    '_token': token,
    'email': 'superadmin@pc.com',
    'password': '123321'
}).encode('utf-8')

req2 = urllib.request.Request('http://localhost:8000/login', data=data, method='POST')
if cookie:
    req2.add_header('Cookie', cookie.split(';')[0]) # Add session cookie back

try:
    with urllib.request.urlopen(req2) as response2:
        print("POST status:", response2.status)
        print("Location:", response2.headers.get('Location'))
except urllib.error.HTTPError as e:
    print("POST error:", e.code)
    print("Headers:", e.headers)
