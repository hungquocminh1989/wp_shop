#!/usr/bin/env python3.8
# -*- coding: utf-8 -*-

"""
#-----------------------------------------------------------------------------
# Name:
#
# Purpose:
#
# Version:     1.1
#
# Author:
#
# Created:     06/02/2020
# Updated:     06/02/2020
#
# Copyright:   -
#
#-----------------------------------------------------------------------------
#Check and update outdated packages
#pip install pipupgrade
#pipupgrade --check
#pipupgrade --latest --yes
#
#Export/Import environments
#pip freeze -l > requirements.txt
#pip install -r /path/to/requirements.txt
#-----------------------------------------------------------------------------
"""

#Import packages
import sys, os, json, requests, pycurl, certifi
from datetime import datetime
from urllib.parse import urlencode

class Shared:
    def curl(self, method, url, data):

        crl = pycurl.Curl()

        crl.setopt(crl.CAINFO, certifi.where())

        #print('Post data : ')
        #print(data)

        postfields = urlencode(data) if data != None else None

        if method == "POST":

            crl.setopt(crl.URL, url)

            crl.setopt(crl.POSTFIELDS, postfields)

        elif method == "DELETE":

            crl.setopt(crl.URL, url)

            crl.setopt(crl.POSTFIELDS, postfields)

            crl.setopt(crl.CUSTOMREQUEST, 'DELETE')

        elif method == "GET":

            if postfields != None:
                if "?" in url:
                    #True
                    crl.setopt(crl.URL, url + "&" + postfields)
                else:
                    #False
                    crl.setopt(crl.URL, url + "?" + postfields)
            else:
                crl.setopt(crl.URL, url)

        result = crl.perform_rs()

        crl.close()

        result = json.loads(result)
        #print('Response info : ')
        #print(result)

        return result
