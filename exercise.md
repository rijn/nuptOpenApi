##NUPT Open API Exercise 模块
Exercise模块提供南邮体育部晨跑服务器接口，通过exercise可以访问学生跑操记录。

###方法列表

* Fetch 方法

---
###Fetch方法
通过Fetch方法，获取体育部服务器数据。

	HTTP请求方式：GET
	http://nuptopenapi.pixelnfinite.com/exercise
	
####参数

	{
		"method"  : "fetch",
		"type" : "",          /* 数据类型 */
		"student_id" : "",    /* 用户学号 */
		"student_name" : "",  /* 用户姓名 */
	}
	
####数据类型

	TIMES  => 跑操次数
	DETAIL => 详细跑操记录

####样例

	http://nuptopenapi.pixelnfinite.com/exercise?method=fetch&type=TIMES&student_id=B14010315&student_name=******
		
####返回

    {
        "query": "success",
        "times": "27",
    }
    
####样例

	http://nuptopenapi.pixelnfinite.com/exercise?method=fetch&type=DETAIL&student_id=B14010315&student_name=******
		
####返回

    {
        "query": "success",
        "times": "27",
        "list": [
            "2015-04-17 07:06",
            "2015-04-16 07:04",
            "2015-04-15 06:59",
            "2015-04-14 07:04",
			...
			/* 返回详细时间 */
        ]
    }
   
####错误
错误时，NOA会返回error字段和错误提示。

	[
		"error" : "1",
		"msg"   : "",
	]	

错误信息

	Wrong parameter         => 请求参数出错
	Network error           => 网络延时或体育部服务器访问出错
	Wrong authenticityToken => 页面解析错误
