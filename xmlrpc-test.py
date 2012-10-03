#!/bin/python3

import sys
import xmlrpc.client

apikey = "8YBjNqa8zFyTs3lZqqqNnkC8KT3KQsxOfWWQaUNAq9jI2vxgJLRUWkSu1M2H2ok5a6MbQpB4oICe1YjAz0lj83E0DwvwKFrJz1Ige7asFBvxnEVKs6UrYmfpGyLf41Mr"
address = "http://localhost/php-virt-control/xmlrpc.php"
selections = ['Information', 'Domain', 'Network']
info_types = ['connection', 'node', 'cpustats', 'eachcpustats', 'memstats', 'system']
domactions = ['Start', 'Stop', 'Reboot', 'Dump', 'Migrate', 'Get screenshot']
request = {'apikey': apikey,
           'connection': {'uri': 'qemu:///system'}
         }

request_info = request
request_info['data'] = {'type': 'unknown'}

request_domain = request
request_domain['data'] = {'name': 'x'}

def choose(prompt, chooser, types):
    print("\n%s types:\n" % chooser)
    num = 0
    for onetype in types:
        print("\t%s) %s" % (num + 1, types[num]))
        num += 1
    print("\n")
    line = input(prompt)
    try:
        return int(line) - 1
    except:
        return -1

try:
    print("XML RPC Proxy is set to %s" % address)
    if input("Is that OK? (Y/n) ") == "n":
        address = input("Enter new address: ")

    proxy = xmlrpc.client.ServerProxy(address)
    num = choose("Enter type: ", "Type", selections)
    if num == 0:
        num = choose("Enter your choice: ", "Information", info_types)
        if num > -1:
            request['data']['type'] = info_types[num]

            print("Result is: %s" % proxy.Information.get(request_info))
    elif num == 1:
        l = proxy.Domain.list(request)
        print("Domains:\n")
        for d in l:
            print("%s) %s" % (int(d) + 1, l[d]))
        print("\n")
        line = input("Choose domain index: ")
        try:
            idx = int(line) - 1
        except:
            sys.exit(1)

        # Assign the name to request_domain dictionary
        name = l[str(idx)]
        request_domain['data']['name'] = name

        l = proxy.Domain.info(request_domain)
        print("\nDomain information:\n\nName: %s\nvCPUs: %s\nState: %s\nMemory: %s MiB (max %s MiB)\nCPUUsed: %s" %
            (name, l['nrVirtCpu'], l['state'], l['memory'] / 1024, l['maxMem'] / 1024, l['cpuUsed']))
        print("\nFeatures: %s\nMultimedia:\n\tInput: %s\n\tVideo: %s\n\tConsole: %s\n\tGraphics: %s\nHost devices: %s\nBoot devices: %s\n" %
            (l['features'], l['multimedia']['input'], l['multimedia']['video'], l['multimedia']['console'],
             l['multimedia']['graphics'], l['devices'], l['boot_devices']))
        num = choose("Enter your choice: ", "Domain actions", domactions)
        if num == -1:
            sys.exit(1)

        # Process actions
        if num == 0:
            print("Starting up domain %s" % name)
            print("Method returned: %s" % proxy.Domain.start(request_domain))
        elif num == 1:
            print("Stopping domain %s" % name)
            print("Method returned: %s" % proxy.Domain.stop(request_domain))
        elif num == 2:
            print("Rebooting %s" % name)
            print("Method returned: %s" % proxy.Domain.reboot(request_domain))
        elif num == 3:
            print("Dumping %s" % name)
        elif num == 4:
            print("Migrating %s" % name)
        elif num == 5:
            print("Getting screenshot of %s" % name)

except xmlrpc.client.ProtocolError as err:
    print("A protocol error occurred")
    print("URL: %s" % err.url)
    print("HTTP/HTTPS headers: %s" % err.headers)
    print("Error code: %d" % err.errcode)
    print("Error message: %s" % err.errmsg)
