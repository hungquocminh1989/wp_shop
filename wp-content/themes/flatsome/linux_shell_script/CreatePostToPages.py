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
import sys, os, json, requests, pycurl, certifi, xlrd, cloudinary
import cloudinary.uploader
from datetime import datetime
from urllib.parse import urlencode
from pandas import *

class ImportPostTool:
    def __init__(self):
        command_line_arguments = sys.argv
        self.import_excel_files = command_line_arguments[1] if len(sys.argv) > 1 else os.path.join(os.getcwd(), 'Export_20200601173030.xlsx')
        cloudinary.config( 
            cloud_name = "minty",
            api_key = "826583412123261", 
            api_secret = "Sa3_O7wQUNvwnQELh8U313D5IvQ" 
        )

    def upload_to_cloudinary(self, attachments):
        cloudinary_images = {}
        if len(attachments) > 0:
            for i in range(len(attachments)):
                result = cloudinary.uploader.upload(attachments[i])
                cloudinary_images[result['public_id']] = result['secure_url']

        return cloudinary_images

    def remove_image_cloudinary(self, cloudinary_images):
        if len(cloudinary_images) > 0:
            for public_id in cloudinary_images:
                cloudinary.uploader.destroy(public_id)
        
    def read_excel_import(self):
        
        xls = ExcelFile(self.import_excel_files)
        
        sheet_product = xls.parse('Products')
        sheet_page = xls.parse('Pages')

        dict_excel = {}
        dict_excel['sheet_product'] = sheet_product.to_dict()
        dict_excel['sheet_page'] = sheet_page.to_dict()
        
        return dict_excel

    def upload_multi_photo(self, attachments, token):
        media_fbid = {}

        if len(attachments) > 0:
            for i in attachments:
                r = self.upload_photo_api(photo_url=attachments[i], token=token)
                if len(r) > 0 and r["id"] != "":
                    media_fbid["attached_media[{0}]".format(i)] = "{'media_fbid':'"+r["id"]+"'}"
    
        return media_fbid

    def curl_requests(self, method, url, data):
        
        field = urlencode(data)
        
        headers = {}
        
        if method == "POST":
            
            return requests.post(url, headers, data).json()
        
        elif method == "GET":
            
            return requests.get(url + "?" + field, headers).json()

    def curl(self, method, url, data):

        crl = pycurl.Curl()

        crl.setopt(crl.CAINFO, certifi.where())

        #print('Post data : ')
        #print(data)
        
        postfields = urlencode(data)
        
        if method == "POST":

            crl.setopt(crl.URL, url)

            crl.setopt(crl.POSTFIELDS, postfields)

        elif method == "DELETE":

            crl.setopt(crl.URL, url)

            crl.setopt(crl.POSTFIELDS, postfields)

            crl.setopt(crl.CUSTOMREQUEST, 'DELETE')

        elif method == "GET":
            
            crl.setopt(crl.URL, url + "&" + postfields)

        result = crl.perform_rs()

        crl.close()

        result = json.loads(result)
        print('Response info : ')
        print(result)
        
        return result

    def get_token_info_api(self, token):

        api_url = 'https://graph.facebook.com/v2.10/me?fields=id,name'

        data = {
            "access_token" : token,
        }

        result = self.curl("GET", api_url, data)
        
        return result

    def delete_page_post_api(self, post_id, token):
        api_url = 'https://graph.facebook.com/v4.0/{0}'.format(post_id)
        data = {
            "access_token" : token,
        }
        result = self.curl("DELETE", api_url, data)

        return result

    def get_page_post_api(self, page_id, limit, token):
        api_ur = 'https://graph.facebook.com/v2.10/{0}/feed'.format(page_id)
        data = {
            "access_token" : token,
            'fields' : 'created_time,message,attachments,permalink_url',
            'limit' : limit,
        }
        result = self.curl("GET", api_url, data)

        return result
        

    def create_page_post_api(self, page_id, message, attachments, token):

        api_url = "https://graph.facebook.com/v2.10/{0}/feed".format(page_id)
        
        data = {
            "message" : message,
            "access_token" : token,
        }
        
        media_fbid = self.upload_multi_photo(attachments, token)
        
        if len(media_fbid) > 0:

            #Merge data
            data = {**data, **media_fbid}

            result = self.curl("POST", api_url, data)
            
            return result
        else:
            result = self.curl("POST", api_url, data)
            
            return result
            
        return None

    def upload_photo_api(self, photo_url, token):
        
        #Upload photo unPublished
        data = {
            "url" : photo_url,
            "published" : False,
            "access_token" : token
        }

        # Param input :
        # [url] 
        # [published] = false
        api_url = 'https://graph.facebook.com/v2.10/me/photos'
        result = self.curl("POST", api_url, data)

        return result

    def execute_import(self):
        dict_excel = self.read_excel_import()
        sheet_product = dict_excel['sheet_product']
        sheet_page = dict_excel['sheet_page']
        excel_product_row = len(sheet_product['No.'])
        excel_page_row = len(sheet_page['No.'])
        
        for excel_page_row in range(len(sheet_page['No.'])):
            
            page_token = sheet_page['Token'][excel_page_row].strip()
            token_info = self.get_token_info_api(page_token)

            if len(token_info) > 0:

                page_name = token_info['name']
                page_id = token_info['id']

                print('Page Name : ' + page_name)
                print('Page Id : ' + page_id)
            
                for excel_product_row in range(len(sheet_product['No.'])):
                    product_name = sheet_product['Product Name'][excel_product_row].strip()
                    product_content = sheet_product['Contents'][excel_product_row]
                    product_images_list = sheet_product['Images'][excel_product_row].split('\n')

                    print('Product Name : ' + product_name)

                    #Upload image to cloudinary
                    cloudinary_images = self.upload_to_cloudinary(product_images_list)
                    print(cloudinary_images)
                    
                    #Post
                    self.create_page_post_api(page_id, product_content, cloudinary_images, page_token)

                    #Remvove image at cloudinary
                    self.remove_image_cloudinary(cloudinary_images)
                

#Start application
token = "EAASp3DPmNo8BAOj5AIcuhX9G0EYebbWoZBuOWZBJqbPffbxgdW6IguaCJZAGkcMI2pCMiR5lX4W0u7vNTF1MWYib2NL9Nagtm8D67ghrz6hHf6ib2dF1l7ABinyKCZAN37Xjl1bzCzcKJ6upAYL7IameooDrDIhhqKjiZAjWGzxoQwCnIwQH45KvkdDcZCvoHaQBaleBYEwgZDZD"
page_id = '1322105514589118'
folder_product_files = "C:\\Users"
folder_page_files = "C:\\Users"
attachments = [
    "https://www.donghogiarehcm.com/wp-content/uploads/2019/10/11612642516_75796048-300x300.jpg",
    "https://www.donghogiarehcm.com/wp-content/uploads/2019/10/11545581316_75796048-300x300.jpg",
]

tool = ImportPostTool()
#result = cloudinary.uploader.upload('https://www.donghogiarehcm.com/wp-content/uploads/2019/10/11612642516_75796048-300x300.jpg')
#print(result)
#result = cloudinary.uploader.destroy(result['public_id'])
#print(result)
#exit()
#tool.get_token_info(token)
tool.execute_import()
#tool.create_page_post_api(page_id, datetime.now().strftime('%Y%m%d%H%M%S%f'), attachments, token)
#tool.upload_photo_api("https://www.donghogiarehcm.com/wp-content/uploads/2019/10/11612642516_75796048-300x300.jpg", token)
exit()
