from apscheduler.schedulers.blocking import BlockingScheduler
from apscheduler.triggers.cron import CronTrigger
import time
from datetime import datetime
import json
from MySQL import *
import random
import math

sched = BlockingScheduler()

def kaijiangtime(id,db,dbconfig):
	kjtime_sum=0
	db.query('select uid,create_time,pid as shopid from (select * from '+dbconfig['prefix']+'shop_record order by create_time desc) a group by uid order by create_time desc limit 50')
	kaijiang_time = db.fetchAllRows()
	if kaijiang_time:
		for value in kaijiang_time:
			db.query('INSERT INTO '+dbconfig['prefix']+'shop_kaijiang (uid,pid,create_time,shopid) VALUES(%d,%d,%f,%d)' % (value[0],id,value[1],value[2]))
			kjtime_sum+=int(time.strftime("%H%M%S",time.localtime(value[1]/1000))+str(int(value[1]))[-3:])
		return kjtime_sum

def get_ten(ten,db,dbconfig):
	db.query('select unit,restrictions,restrictions_num from '+dbconfig['prefix']+'ten where id=%d' % (ten))
	return db.fetchOneRow()

def kaijiang_num(num):
    numbers = list(range(10000001,num+10000001))
    random.shuffle(numbers)
    return ','.join(map(str, numbers))

def kjtime(party):
	if party!=1:
		kj_time=int(time.time()/300+1)*300+50
	else:
		kjime=datetime.now().hour
		if kjime>=10 and kjime<22:
			kj_time=int(time.time()/600+1)*600+50
		elif kjime<2 or kjime>=22:
			kj_time=int(time.time()/300+1)*300+50
		else:
			timeStr = datetime.strptime(time.strftime("%Y-%m-%d 10:00:50", time.localtime()),"%Y-%m-%d %H:%M:%S")
			kj_time=int(time.mktime(timeStr.timetuple()))
	return kj_time

def my_job():
	kjime=datetime.now().hour
	job=sched.get_job('my_job')
	if kjime>=8 and kjime<22:
		if str(job.trigger)!=str(CronTrigger(second='*/5')):
			sched.reschedule_job(job.id, trigger='cron',second='*/5')
	elif kjime<2 or kjime>=22:
		if str(job.trigger)!=str(CronTrigger(second='*/20')):
			sched.reschedule_job(job.id, trigger='cron',second='*/20')
	else:
		if str(job.trigger)!=str(CronTrigger(hour='8', second='50')):
			sched.reschedule_job(job.id, trigger='cron', hour='8', second='50')
	user_tz()

def user_tz():
	strs = ','
	f = open("config.json")
	dbconfig = json.load(f)
	f.close
	db = MySQL(dbconfig)
	uid=random.randint(100001,101500)
	db.query('SELECT id,price,status,edit_price,ten FROM '+dbconfig['prefix']+'shop where auto=1 order by rand() LIMIT 1')
	shop = db.fetchOneRow()
	db.query('SELECT id,no,number,state,jiang_num,zj_uid FROM '+dbconfig['prefix']+'shop_period where state=0 and sid=%d' % (shop[0]))
	period = db.fetchOneRow()
	if period:
		surplus=shop[1]-period[2]
		if shop[1]/100<1:
			cathectic=1
		else:
			cathectic=random.randint(1,math.ceil(shop[1]/100))
		if(shop[4]>0):
			ten=get_ten(shop[4],db,dbconfig)
			if(ten[0]>1 and cathectic%ten[0]!=0):
				cathectic=int(cathectic/ten[0]+1)*ten[0]
			if(ten[1]>0 and cathectic>ten[2]):
				cathectic=ten[2]
		if cathectic>surplus:
			cathectic=surplus
		jiang_num=period[4].split(strs)
		db.insert('INSERT INTO '+dbconfig['prefix']+'shop_record (uid,pid,number,create_time,num) VALUES(%d,%d,%d,%d,"%s")' % (uid,period[0],cathectic,(int(time.time()*1000)+random.randint(0, 600)),strs.join(jiang_num[0:cathectic])))
		del jiang_num[0:cathectic]
		if (surplus-cathectic)==0:
			if shop[3]!=shop[1]:
				db.query('update '+dbconfig['prefix']+'shop set price=%d,edit_price=%d where id=%d' % (shop[3],shop[3],shop[0]))
			kaijiang_count=kaijiangtime(period[0],db,dbconfig)
			if dbconfig['zb']:
				db.query('select sum(number) from '+dbconfig['prefix']+'shop_record where pid=%d and order_id=""' % (period[0]))
				tz_count=db.fetchOneRow()
				if (tz_count[0]/shop[1])*100>=50 or period[5]:
					zjnum=kaijiang_count%shop[1]+10000001
					db.query('select uid from '+dbconfig['prefix']+'shop_record where pid=%d and FIND_IN_SET("%d",num)' % (period[0],zjnum))
					zjuid = db.fetchOneRow()
					if zjuid[0]>101500 or period[5]:
						if period[5]:
							uid=period[5]
						db.query('select group_concat(num) as num from '+dbconfig['prefix']+'shop_record where uid=%d and pid=%d GROUP BY uid,pid' % (uid,period[0]))
						zjuser = db.fetchOneRow()
						if not zjuser:
							db.query('select group_concat(num) as num from '+dbconfig['prefix']+'shop_record where uid<%d and pid=%d GROUP BY uid,pid' % (uid,period[0]))
							zjuser = db.fetchOneRow()
						user_num=zjuser[0].split(strs)
						zj_num=user_num[random.randrange(0,len(user_num))]
						if zj_num:
							zjc=int(zj_num)-zjnum
							kaijiang_count=kaijiang_count+zjc
							if zjc>0:
								db.query('update '+dbconfig['prefix']+'shop_kaijiang set create_time=create_time+1 where pid=%d ORDER BY id desc limit %d' % (period[0],abs(zjc)))
							elif zjc<0:
								db.query('update '+dbconfig['prefix']+'shop_kaijiang set create_time=create_time-1 where pid=%d ORDER BY id asc limit %d' % (period[0],abs(zjc)))
			db.query('update '+dbconfig['prefix']+'shop_period set state=1,number=%d,kaijang_time=%d,kaijiang_count=%d,jiang_num="%s",end_time=%d where id=%d' % (period[2]+cathectic,kjtime(dbconfig['party']),kaijiang_count,strs.join(jiang_num),int(time.time()*1000),period[0]))
			if shop[2]>0:
				db.insert('INSERT INTO '+dbconfig['prefix']+'shop_period (jiang_num,sid,create_time,state,no) VALUES("%s",%d,%d,0,%d)' % (kaijiang_num(shop[1]),shop[0],int(time.time()),period[1]+1))
		else:
			db.query('update '+dbconfig['prefix']+'shop_period set number=%d,jiang_num="%s" where id=%d' % (period[2]+cathectic,strs.join(jiang_num),period[0]))
	db.close()
	
dtime=datetime.now().hour
if dtime>=8 and dtime<22:
	sched.add_job(my_job, 'cron', second='*/5',id='my_job')
elif dtime<2 or dtime>=22:
	sched.add_job(my_job, 'cron', second='*/20',id='my_job')
else:
	sched.add_job(my_job, 'cron', hour='8',second='50',id='my_job')
sched.start()