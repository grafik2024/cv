#!/usr/bin/env python3
"""Fake UPnP Internet Gateway Device for testing WinUAE netplay UPnP.

Answers SSDP M-SEARCH (multicast 239.255.255.250:1900), serves a device
description (chunked) and the WANIPConnection SOAP actions. Logs every
SOAP call to stdout.

usage: fake_igd.py HTTP_PORT [--only-permanent] [--conflict IP]
"""
import socket, struct, sys, threading, re
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

HTTP_PORT = int(sys.argv[1])
ONLY_PERMANENT = '--only-permanent' in sys.argv
CONFLICT = sys.argv[sys.argv.index('--conflict') + 1] if '--conflict' in sys.argv else None
SERVICE = 'urn:schemas-upnp-org:service:WANIPConnection:1'
mappings = {}

DESC = f"""<?xml version="1.0"?>
<root xmlns="urn:schemas-upnp-org:device-1-0">
<device><deviceType>urn:schemas-upnp-org:device:InternetGatewayDevice:1</deviceType>
<serviceList><service><serviceType>urn:schemas-upnp-org:service:Layer3Forwarding:1</serviceType>
<controlURL>/ctl/L3F</controlURL></service></serviceList>
<deviceList><device><deviceType>urn:schemas-upnp-org:device:WANDevice:1</deviceType>
<deviceList><device><deviceType>urn:schemas-upnp-org:device:WANConnectionDevice:1</deviceType>
<serviceList><service>
<serviceType>{SERVICE}</serviceType>
<serviceId>urn:upnp-org:serviceId:WANIPConn1</serviceId>
<controlURL>/ctl/IPConn</controlURL>
</service></serviceList></device></deviceList></device></deviceList></device></root>
"""


def envelope(action, inner):
    return (f'<?xml version="1.0"?><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">'
            f'<s:Body><u:{action}Response xmlns:u="{SERVICE}">{inner}</u:{action}Response></s:Body></s:Envelope>')


def fault(code):
    return ('<?xml version="1.0"?><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body>'
            '<s:Fault><faultcode>s:Client</faultcode><faultstring>UPnPError</faultstring><detail>'
            f'<UPnPError xmlns="urn:schemas-upnp-org:control-1-0"><errorCode>{code}</errorCode>'
            '<errorDescription>error</errorDescription></UPnPError></detail></s:Fault></s:Body></s:Envelope>')


def arg(body, name):
    m = re.search(f'<{name}>(.*?)</{name}>', body, re.S)
    return m.group(1) if m else ''


class H(BaseHTTPRequestHandler):
    protocol_version = 'HTTP/1.1'

    def log_message(self, *a):
        pass

    def send(self, code, body, chunked=False):
        data = body.encode()
        self.send_response(code)
        self.send_header('Content-Type', 'text/xml')
        if chunked:
            self.send_header('Transfer-Encoding', 'chunked')
            self.end_headers()
            for i in range(0, len(data), 100):
                c = data[i:i + 100]
                self.wfile.write(b'%x\r\n' % len(c) + c + b'\r\n')
            self.wfile.write(b'0\r\n\r\n')
        else:
            self.send_header('Content-Length', str(len(data)))
            self.end_headers()
            self.wfile.write(data)
        self.close_connection = True

    def do_GET(self):
        print('GET', self.path, flush=True)
        if self.path == '/rootDesc.xml':
            self.send(200, DESC, chunked=True)
        else:
            self.send(404, 'no')

    def do_POST(self):
        n = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(n).decode()
        action = self.headers.get('SOAPAction', '').strip('"').split('#')[-1]
        print('SOAP', action, {k: arg(body, k) for k in ('NewExternalPort', 'NewInternalClient', 'NewLeaseDuration', 'NewProtocol') if arg(body, k)}, flush=True)
        if self.path != '/ctl/IPConn':
            return self.send(404, 'no')
        port = arg(body, 'NewExternalPort')
        if action == 'AddPortMapping':
            if ONLY_PERMANENT and arg(body, 'NewLeaseDuration') != '0':
                return self.send(500, fault(725))
            if CONFLICT:
                mappings[port] = CONFLICT
                return self.send(500, fault(718))
            mappings[port] = arg(body, 'NewInternalClient')
            return self.send(200, envelope(action, ''))
        if action == 'GetSpecificPortMappingEntry':
            if port in mappings:
                return self.send(200, envelope(action, f'<NewInternalPort>{port}</NewInternalPort><NewInternalClient>{mappings[port]}</NewInternalClient>'))
            return self.send(500, fault(714))
        if action == 'GetExternalIPAddress':
            return self.send(200, envelope(action, '<NewExternalIPAddress>203.0.113.7</NewExternalIPAddress>'))
        if action == 'DeletePortMapping':
            mappings.pop(port, None)
            return self.send(200, envelope(action, ''))
        self.send(500, fault(401))


def ssdp():
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM, socket.IPPROTO_UDP)
    s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    s.bind(('', 1900))
    mreq = struct.pack('4sl', socket.inet_aton('239.255.255.250'), socket.INADDR_ANY)
    s.setsockopt(socket.IPPROTO_IP, socket.IP_ADD_MEMBERSHIP, mreq)
    while True:
        data, addr = s.recvfrom(2048)
        text = data.decode(errors='replace')
        if not text.startswith('M-SEARCH'):
            continue
        st = re.search(r'^ST:\s*(\S+)', text, re.I | re.M)
        print('SSDP from', addr, st.group(1) if st else '?', flush=True)
        resp = ('HTTP/1.1 200 OK\r\nCACHE-CONTROL: max-age=120\r\n'
                f'ST: {st.group(1) if st else "upnp:rootdevice"}\r\nUSN: uuid:fake::igd\r\nEXT:\r\n'
                f'Location: http://127.0.0.1:{HTTP_PORT}/rootDesc.xml\r\nSERVER: fake/1.0 UPnP/1.1\r\n\r\n')
        s.sendto(resp.encode(), addr)


threading.Thread(target=ssdp, daemon=True).start()
ThreadingHTTPServer(('127.0.0.1', HTTP_PORT), H).serve_forever()
