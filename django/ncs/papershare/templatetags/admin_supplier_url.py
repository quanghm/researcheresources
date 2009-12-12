#-*-coding:UTF-8-*-
import re
from django import  template
register = template.Library()

class CustomURL(template.Node):
    def __init__(self, request_string, key, value):
        self.request_string = template.Variable(request_string)
        self.key = key
        self.value = template.Variable(value)

    def render(self, context):
        request_string = re.sub('sort=[a-z]+', '', self.request_string.resolve(context))
        request_string = re.sub('&&', '&', request_string)
        return re.sub('%s=[a-zA-Z0-9_-]+'%(self.key), '', \
                      request_string)

def custom_request(parser, token):
    try:
        # split_contents() knows not to split quoted strings.
        tag_name, request_string, key, value = token.split_contents()
    except ValueError:
        raise template.TemplateSyntaxError, "%r tag requires exactly three arguments" % token.contents.split()[0]
    return CustomURL(request_string, key, value)
register.tag('custom_request', custom_request)        