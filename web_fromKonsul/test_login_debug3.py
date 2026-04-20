import urllib.request
import urllib.parse
import re

req1 = urllib.request.Request('http://localhost:8000/login', method='GET')
try:
    with urllib.request.urlopen(req1) as response1:
        html = response1.read().decode('utf-8')
        cookies = response1.headers.get_all('Set-Cookie') # get ALL cookies
        print("GET status:", response1.status)
        match = re.search(r'<input[^>]+name="_token"[^>]+value="([^"]+)"', html)
        if not match:
            print("Token not found")
            exit(1)
        token = match.group(1)
except Exception as e:
    print("GET error:", e)
    exit(1)

data = urllib.parse.urlencode({
    '_token': token,
    'email': 'superadmin@pc.com',
    'password': '123321'
}).encode('utf-8')

req2 = urllib.request.Request('http://localhost:8000/login', data=data, method='POST')

cookie_str = ""
if cookies:
    # combine all cookies for the Cookie header
    for c in cookies:
        cookie_str += c.split(';')[0] + "; "

req2.add_header('Cookie', cookie_str)
req2.add_header('Content-Type', 'application/x-www-form-urlencoded')

class NoRedirectHandler(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None  # Stop redirect

opener = urllib.request.build_opener(NoRedirectHandler)

try:
    response2 = opener.open(req2)
    print("POST status:", response2.status)
    print("Headers:", response2.headers)
    print("Body:", response2.read().decode('utf-8')[:500])
except urllib.error.HTTPError as e:
    print("POST error status:", e.code)
    print("Headers:", e.headers)
    print("Body:", e.read().decode('utf-8')[:500])
