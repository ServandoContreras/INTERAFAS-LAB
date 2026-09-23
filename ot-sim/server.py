from http.server import BaseHTTPRequestHandler, HTTPServer
import json, os, time

STATE={
    "plant":"Planta Metropolitana Norte","tank":78,"flow":840,"pressure":4.2,
    "p101":"ON","p102":"STANDBY","v201":"OPEN","quality":"NORMAL","alarms":[],
    "firmware":{"device":"RTU-GW-07","version":"3.4.2","mode":"NORMAL","diagnostic":"LOCKED","updated_at":None}
}

def refresh_process():
    if STATE["p101"] == "OFF":
        STATE["flow"] = 455
        STATE["pressure"] = 2.5
        STATE["alarms"] = ["FLOW_LOW","PRESSURE_LOW"]
    else:
        STATE["flow"] = 840
        STATE["pressure"] = 4.2
        STATE["alarms"] = []

class H(BaseHTTPRequestHandler):
    def _body(self):
        try:
            n=int(self.headers.get('Content-Length','0'))
            return json.loads(self.rfile.read(n).decode() or '{}') if n else {}
        except Exception:
            return {}
    def _send(self, code, data):
        body=json.dumps(data,ensure_ascii=False).encode()
        self.send_response(code)
        self.send_header('Content-Type','application/json; charset=utf-8')
        self.send_header('Content-Length',str(len(body)))
        self.end_headers(); self.wfile.write(body)
    def do_GET(self):
        refresh_process()
        if self.path=='/health': return self._send(200,{"ok":True})
        if self.path=='/state': return self._send(200,STATE)
        if self.path=='/firmware': return self._send(200,STATE['firmware'])
        return self._send(404,{"error":"not_found"})
    def do_POST(self):
        data=self._body()
        if self.path=='/control':
            asset=str(data.get('asset',''))
            state=str(data.get('state','')).upper()
            if asset!='P-101' or state not in ('ON','OFF'):
                return self._send(400,{"ok":False,"error":"invalid_control"})
            previous=STATE['p101']; STATE['p101']=state; refresh_process()
            return self._send(200,{"ok":True,"asset":asset,"previous":previous,"state":state,"process":STATE})
        if self.path=='/firmware':
            pkg=data.get('package') if isinstance(data.get('package'),dict) else data
            if pkg.get('device')!='RTU-GW-07': return self._send(400,{"ok":False,"error":"device_mismatch"})
            version=str(pkg.get('version','')).strip()
            if not version: return self._send(400,{"ok":False,"error":"version_required"})
            previous=dict(STATE['firmware'])
            STATE['firmware']={
                "device":"RTU-GW-07","version":version,
                "mode":str(pkg.get('mode','NORMAL'))[:32],
                "diagnostic":str(pkg.get('diagnostic','LOCKED'))[:32],
                "updated_at":int(time.time())
            }
            return self._send(200,{"ok":True,"previous":previous,"firmware":STATE['firmware'],"reboot":"completed"})
        return self._send(404,{"error":"not_found"})
    def log_message(self, *args): pass

HTTPServer(('0.0.0.0',int(os.getenv('PORT','8081'))),H).serve_forever()
