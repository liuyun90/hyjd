#coding=utf-8
import urllib2
import json
class WechatPush(object):

	def __init__(self,appid,secrect):
		self.appid = appid
		self.secrect = secrect
		self.token = ''
		self.requst_url ="https://api.weixin.qq.com/cgi-bin/"

	#获取accessToken
	def getToken(self):
		if not self.token.strip():
			url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='+self.appid +"&secret="+self.secrect
			f = urllib2.urlopen(url)
			s = f.read().decode()
			#读取json数据
			j = json.loads(s)
			j.keys()
			token = j['access_token']
			self.token = token
		return self.token

	#开始推送
	def do_push(self,urls,dict_arr):
		token = self.getToken()
		json_template = json.dumps(dict_arr,ensure_ascii=False)
		requst_url = "%s%s?access_token=%s" % (self.requst_url,urls,token)
		content = self.post_data(requst_url,json_template)
		#读取json数据
		j = json.loads(content)
		j.keys()
		errcode = j['errcode']
		if errcode==42001:
			self.token = ''
			do_push(self,dict_arr)
		return content



	#模拟post请求
	def post_data(self,url,para_data):
		f = urllib.request.urlopen(url,para_data.encode(encoding="utf-8"))
		content = f.read().decode()
		return content