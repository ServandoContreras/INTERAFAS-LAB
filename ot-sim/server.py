from http.server import BaseHTTPRequestHandler, HTTPServer
import json, os, time

STATE={
    "plant":"Planta Metropolitana Norte",
    "tank":78,
    "flow":840,
    "pressure":4.2,
    "p101":"ON",
    "p102":"STANDBY",
    "v201":"OPEN",
    "quality":"NORMAL",
    "alarms":[],
    "alarm_count":0,
    "process_state":"NORMAL",
    "zone_a":"NORMAL",
    "availability":99.82,
    "stations_critical":0,
    "incident":{
        "active":False,
        "profile":None,
        "started_at":None,
        "stage":0,
        "stage_label":"NORMAL",
        "progress":0
    },
    "firmware":{
        "device":"RTU-GW-07",
        "version":"3.4.2",
        "mode":"NORMAL",
        "diagnostic":"LOCKED",
        "pressure_setpoint_bar":4.2,
        "updated_at":None
    }
}

CASCADE=[
    {
        "after":0,
        "label":"INITIALIZATION ANOMALY",
        "flow":900,
        "pressure":4.7,
        "tank":80,
        "alarms":18,
        "availability":98.4,
        "critical":0,
        "quality":"WARNING",
        "zone":"UNSTABLE",
        "alarm_names":["FIRMWARE_STATE_MISMATCH","RTU_COMMAND_RATE_HIGH"]
    },
    {
        "after":3,
        "label":"CONTROL INSTABILITY",
        "flow":1080,
        "pressure":5.3,
        "tank":83,
        "alarms":96,
        "availability":94.7,
        "critical":1,
        "quality":"WARNING",
        "zone":"DEGRADED",
        "alarm_names":["PRESSURE_HIGH","FLOW_DEVIATION","PUMP_SEQUENCE_CONFLICT","RTU_COMMAND_RATE_HIGH"]
    },
    {
        "after":6,
        "label":"PROCESS CASCADE",
        "flow":1320,
        "pressure":6.1,
        "tank":87,
        "alarms":486,
        "availability":82.3,
        "critical":4,
        "quality":"DEGRADED",
        "zone":"CRITICAL",
        "alarm_names":["PRESSURE_HIGH_HIGH","FLOW_HIGH","VALVE_POSITION_CONFLICT","PUMP_INTERLOCK_BYPASS","QUALITY_SAMPLE_LOSS"]
    },
    {
        "after":9,
        "label":"METROPOLITAN PROPAGATION",
        "flow":1560,
        "pressure":7.0,
        "tank":92,
        "alarms":1488,
        "availability":61.5,
        "critical":8,
        "quality":"ALARM",
        "zone":"CRITICAL",
        "alarm_names":["PRESSURE_HIGH_HIGH","FLOW_HIGH_HIGH","PLC_STATE_CONFLICT","RTU_TIMEOUT_STORM","ZONE_A_SUPPLY_RISK","ZONE_B_SUPPLY_RISK"]
    },
    {
        "after":12,
        "label":"SYSTEMIC FAILURE",
        "flow":1810,
        "pressure":7.9,
        "tank":97,
        "alarms":3276,
        "availability":28.8,
        "critical":11,
        "quality":"UNSAFE",
        "zone":"EMERGENCY",
        "alarm_names":["PRESSURE_HIGH_HIGH","PUMP_OVERSPEED","VALVE_COMMAND_STORM","PLC_WATCHDOG","RTU_TIMEOUT_STORM","QUALITY_UNSAFE"]
    },
    {
        "after":15,
        "label":"CATASTROPHIC STATE",
        "flow":1980,
        "pressure":8.7,
        "tank":99,
        "alarms":6842,
        "availability":4.1,
        "critical":12,
        "quality":"UNSAFE",
        "zone":"FAILURE",
        "alarm_names":["METROPOLITAN_CONTROL_LOSS","PRESSURE_HIGH_HIGH","FLOW_HIGH_HIGH","PUMP_OVERSPEED","VALVE_COMMAND_STORM","PLC_WATCHDOG","RTU_TIMEOUT_STORM","QUALITY_UNSAFE"]
    }
]

def reset_process():
    STATE["tank"]=78
    STATE["flow"]=840
    STATE["pressure"]=4.2
    STATE["p101"]="ON"
    STATE["p102"]="STANDBY"
    STATE["v201"]="OPEN"
    STATE["quality"]="NORMAL"
    STATE["alarms"]=[]
    STATE["alarm_count"]=0
    STATE["process_state"]="NORMAL"
    STATE["zone_a"]="NORMAL"
    STATE["availability"]=99.82
    STATE["stations_critical"]=0
    STATE["incident"]={
        "active":False,
        "profile":None,
        "started_at":None,
        "stage":0,
        "stage_label":"NORMAL",
        "progress":0
    }

def refresh_process():
    incident=STATE.get("incident",{})
    if incident.get("active") and incident.get("profile")=="CASCADE":
        elapsed=max(0,time.time()-float(incident.get("started_at") or time.time()))
        stage=0
        for i,item in enumerate(CASCADE):
            if elapsed>=item["after"]:
                stage=i
        item=CASCADE[stage]
        STATE["tank"]=item["tank"]
        STATE["flow"]=item["flow"]
        STATE["pressure"]=item["pressure"]
        STATE["p101"]="ON"
        STATE["p102"]="ON" if stage>=1 else "STANDBY"
        STATE["v201"]="OSCILLATING" if stage>=2 else "OPEN"
        STATE["quality"]=item["quality"]
        STATE["alarms"]=item["alarm_names"]
        STATE["alarm_count"]=item["alarms"]
        STATE["process_state"]="CATASTROPHIC" if stage==5 else ("CRITICAL" if stage>=2 else "UNSTABLE")
        STATE["zone_a"]=item["zone"]
        STATE["availability"]=item["availability"]
        STATE["stations_critical"]=item["critical"]
        STATE["incident"]["stage"]=stage
        STATE["incident"]["stage_label"]=item["label"]
        STATE["incident"]["progress"]=int(round((stage/5)*100))
        STATE["incident"]["elapsed_seconds"]=int(elapsed)
        return

    if STATE["p101"] == "OFF":
        STATE["flow"] = 455
        STATE["pressure"] = 2.5
        STATE["process_state"] = "DEGRADED"
        STATE["zone_a"] = "DEGRADED"
        STATE["quality"]="NORMAL"
        STATE["alarms"] = ["FLOW_LOW","PRESSURE_LOW","ZONE_A_SUPPLY_RISK"]
        STATE["alarm_count"]=3
        STATE["availability"]=96.4
        STATE["stations_critical"]=1
    else:
        STATE["flow"] = 840
        STATE["pressure"] = 4.2
        STATE["process_state"] = "NORMAL"
        STATE["zone_a"] = "NORMAL"
        STATE["quality"]="NORMAL"
        STATE["alarms"] = []
        STATE["alarm_count"]=0
        STATE["availability"]=99.82
        STATE["stations_critical"]=0

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
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        refresh_process()
        if self.path=='/health':
            return self._send(200,{"ok":True})
        if self.path=='/state':
            return self._send(200,STATE)
        if self.path=='/firmware':
            return self._send(200,STATE['firmware'])
        return self._send(404,{"error":"not_found"})

    def do_POST(self):
        data=self._body()

        if self.path=='/control':
            refresh_process()
            if STATE.get("incident",{}).get("active"):
                return self._send(423,{"ok":False,"error":"firmware_override_active","process":STATE})
            asset=str(data.get('asset',''))
            state=str(data.get('state','')).upper()
            if asset!='P-101' or state not in ('ON','OFF'):
                return self._send(400,{"ok":False,"error":"invalid_control"})
            previous=STATE['p101']
            STATE['p101']=state
            refresh_process()
            return self._send(200,{"ok":True,"asset":asset,"previous":previous,"state":state,"process":STATE})

        if self.path=='/firmware':
            pkg=data.get('package') if isinstance(data.get('package'),dict) else data
            if pkg.get('device')!='RTU-GW-07':
                return self._send(400,{"ok":False,"error":"device_mismatch"})
            version=str(pkg.get('version','')).strip()
            if not version:
                return self._send(400,{"ok":False,"error":"version_required"})

            previous=dict(STATE['firmware'])
            mode=str(pkg.get('mode',previous.get('mode','NORMAL')))[:32].upper()
            diagnostic=str(pkg.get('diagnostic',previous.get('diagnostic','LOCKED')))[:32].upper()

            try:
                pressure_setpoint=float(pkg.get('pressure_setpoint_bar',previous.get('pressure_setpoint_bar',4.2)))
            except Exception:
                return self._send(400,{"ok":False,"error":"invalid_pressure_setpoint"})

            STATE['firmware']={
                "device":"RTU-GW-07",
                "version":version,
                "mode":mode,
                "diagnostic":diagnostic,
                "pressure_setpoint_bar":round(pressure_setpoint,2),
                "updated_at":int(time.time())
            }

            unsafe_setpoint=round(pressure_setpoint,2)!=4.2

            if unsafe_setpoint:
                STATE["incident"]={
                    "active":True,
                    "profile":"CASCADE",
                    "trigger":"pressure_setpoint_bar",
                    "requested_value":round(pressure_setpoint,2),
                    "baseline_value":4.2,
                    "started_at":time.time(),
                    "stage":0,
                    "stage_label":"INITIALIZATION ANOMALY",
                    "progress":0,
                    "elapsed_seconds":0
                }
                refresh_process()
            elif STATE.get("incident",{}).get("active"):
                reset_process()
                STATE['firmware']={
                    "device":"RTU-GW-07",
                    "version":version,
                    "mode":mode,
                    "diagnostic":diagnostic,
                    "pressure_setpoint_bar":round(pressure_setpoint,2),
                    "updated_at":int(time.time())
                }

            return self._send(200,{
                "ok":True,
                "previous":previous,
                "firmware":STATE['firmware'],
                "reboot":"completed",
                "incident":STATE.get("incident")
            })

        return self._send(404,{"error":"not_found"})

    def log_message(self, *args):
        pass

HTTPServer(('0.0.0.0',int(os.getenv('PORT','8081'))),H).serve_forever()
