#!/bin/python3

SERVER="http://localhost/virt-control/xmlrpc.php"
APIKEY="apikey"
ID_CONNECTION=1
DOMAIN="test"
NETWORK="default"
USER="test"

import xmlrpc.client

proxy = xmlrpc.client.ServerProxy(SERVER)

try:
    # Get all connections for user
    print("Result is: %s" % proxy.Connection.GetUserConnections({'apikey': APIKEY}))

    # Get all domains on connection
    # print("Result is: %s" % proxy.Connection.ListDomains({'apikey': APIKEY, 'connection': ID_CONNECTION}))

    # Control specified domain, possible actions are: start, shutdown, destroy, suspend, resume
    # print("Result is: %s" % proxy.Connection.DomainStart({'apikey': APIKEY, 'connection': ID_CONNECTION, 'name': DOMAIN}))
    # print("Result is: %s" % proxy.Connection.DomainControl({'apikey': APIKEY, 'connection': ID_CONNECTION, 'name': DOMAIN, 'action': 'start'}))

    # Dump domain information
    # print("Result is: %s" % proxy.Connection.DomainDumpXML({'apikey': APIKEY, 'connection': ID_CONNECTION, 'name': DOMAIN}))

    # Get all networks on connection
    # print("Result is: %s" % proxy.Connection.ListNetworks({'apikey': APIKEY, 'connection': ID_CONNECTION}))

    # Control specific network, possible actions are: start, stop
    # print("Result is: %s" % proxy.Connection.NetworkStart({'apikey': APIKEY, 'connection': ID_CONNECTION, 'name': NETWORK}))
    # print("Result is: %s" % proxy.Connection.NetworkControl({'apikey': APIKEY, 'connection': ID_CONNECTION, 'name': NETWORK, 'action': 'stop'}))

    # Dump network information
    # print("Result is: %s" % proxy.Connection.NetworkDumpXML({'apikey': APIKEY, 'connection': ID_CONNECTION, 'name': NETWORK}))

    # Change password of user 'test' to 'test', requires edit user permissions
    # print("Result is: %s" % proxy.User.ChangePassword({'apikey': APIKEY, 'username': USER, 'password': 'test'}))

    # Request password reset
    # print("Result is: %s" % proxy.User.ResetPassword({'apikey': APIKEY, 'username': USER}))
except xmlrpc.client.ProtocolError as err:
    print("Error: %s" % err.errmsg)
