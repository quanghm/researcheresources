from django.http import HttpResponse
from django.core.mail import EmailMultiAlternatives
from django.template.loader import render_to_string

from django.conf import settings

from ncs.lib.stripogram import html2text
from ncs.lib.feedparser import _sanitizeHTML

def sendmailFromHtml(fromAddr,toAddr,subject,html_content):
    try:
        text_content = htmlToText(html_content)
    
        msg = EmailMultiAlternatives(subject, text_content, fromAddr, toAddr)
        msg.attach_alternative(html_content, "text/html")
        msg.send()
        
        return True
    except:
        return False

def sendmailFromTemplate(fromAddr=settings.DEFAULT_FROM_EMAIL,toAddr=None,subject=None,template_name=None,context=None):
        #print "----" , subject, fromAddr, toAddr, template_name
    #try:
        if type(toAddr) != list and type(toAddr) != tuple:
            toAddr = [toAddr]
        
        html_content = render_to_string(template_name, context)        
        text_content = htmlToText(html_content)
        #print "------- ", subject, text_content, fromAddr, toAddr
        msg = EmailMultiAlternatives(subject, text_content, fromAddr, toAddr)
        msg.attach_alternative(html_content, "text/html")
        msg.send()
        
        return True
    #except str:
        return False
    
def htmlToText(html):
    return html2text(_sanitizeHTML(html,"utf-8"))

        
