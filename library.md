##NUPT Open API Library 模块
Library模块提供南京邮电大学图书馆检索接口，通过此接口，NOA会把请求转接给OPAC，并将OPAC的查询结果打包成JSON返回给开发者，用以实现图书检索，借阅查询，续借等功能。

###方法列表

* Search
* Detail
* Checkout
* Renew
* Hisroty
* Appointment

---

###Search 方法
开发者通过Search方法，检索南京邮电大学图书馆数据库。

	HTTP请求方式：GET
	http://nuptopenapi.pixelnfinite.com/library

####参数

	{
		"method"  : "search", /* 固定为Search */
		"keyword" : "",       /* 书名或ISBN */
		"start"   : "1",      /* 起始位，格式为5i+1，从1开始，如果不填即为1
		                       * 非5i+1可能会抛出未知错误
		                       */
	}

####样例

	http://nuptopenapi.pixelnfinite.com/library?method=search&keyword=stm32&start=6

####返回

	[
    	{
        	"id": "6",                                      /* 该keyword条目索引 */
        	"name": "基于ARM的单片机应用及实践:STM32案例式教学", /* 书名 */
        	"author": "武奇生 ... [等] 编著",                 /* 作者 */
        	"press": "机械工业出版社 2014",                   /* 出版社 */
        	"category": "TP368.1/3-1066",                   /* 标准分类号 */
        	"type": "中文图书",                              
        	"marc_no": "0000604875",                        /* 数据库书目唯一编号 */
        	"available": 2,                                 /* 仙林图书馆可借数量 */
        	"location": "XLYB样本图书阅览室",                 /* 仙林图书馆馆藏位置 */
    	},
    	...
    	/* 默认返回start后五项 */
    ]
   
####错误
错误时，NOA会返回error字段和错误提示。

	[
		"error" : "1",
		"msg"   : "",
	]	

错误信息

	Wrong parameter => 请求参数出错
	Network error   => 网络延时或OPAC访问出错
	System error    => OPAC参数出错

---

###Detail 方法
Detail方法用以返回书目详细信息。

	HTTP请求方式：GET
	http://nuptopenapi.pixelnfinite.com/library
	
####参数

	{
		"method"  : "detail",
		"marc_no" : "",       /* Search时获得的MARC号码 */
	}
	
####样例

	http://nuptopenapi.pixelnfinite.com/library?method=detail&marc_no=0000601381
	
####返回

    {
        "name": "梦游书",
        "author": "简媜著",
        "press": "北京:九州出版社,2014",
        "isbn": "978-7-5108-2889-8",
        "price": "CNY23.00",
        "carrier": "187页, [3] 页图版:图;21cm",
        "subject": "散文集-中国-当代",
        "category": "I267",
        "summary": "本书收录了《台北小脸盆》、《榕树的早晨》、《水昆兄》、《魔女的厨房》、《女作家的爱情观》、《一只等人的猴子》、《粉圆女人》、《上殿》、《白雪茶树》、《寂寞的冰箱》等作品。",
        "type": "中文图书",
        "view": "9",
        "list": [
            {
                "demanding": "I267/3-4387 ",
                "bar": "1809010",
                "location": "XLWY人文艺术图书阅览室（仙林）",
                "state": "可借"
            },
            {
                "demanding": "I267/3-4387 ",
                "bar": "1809011",
                "location": "XLWY人文艺术图书阅览室（仙林）",
                "state": "可借"
            }
        ]
    }
   
####错误
错误时，NOA会返回error字段和错误提示。

	[
		"error" : "1",
		"msg"   : "",
	]	

错误信息

	Wrong parameter => 请求参数出错
	Network error   => 网络延时或OPAC访问出错
	Wrong marc_no   => 错误的MARC码

---

###Checkout 方法
通过Checkout方法，检索用户当前借阅情况。

	HTTP请求方式：POST
	http://nuptopenapi.pixelnfinite.com/library
	
####参数

	{
		"method"  : "checkout",
		"student_id" : "",       /* 用户学号 */
		"password" : "",         /* OPAC密码 */
		"student_name" : "",     /* 若用户已认证，则此项可跳过，否则系统会自动认证 */
	}
	
####样例

	http://nuptopenapi.pixelnfinite.com/library?method=checkout&student_id=B14010312&password=********&student_name=%E5%8D%9E%E8%BF%9C%E5%93%B2
	
####返回

    {
        "card": "110201400809900",    /* 用户图书卡号 */
        "times": "20",                /* 累计借阅量 */
        "state": ".2",                /* 欠款状态 */
        "list": [
            {
                "bar": "1547970",                         /* 图书条码 */
                "marc_no": "0000455211",                  /* MARC码，用以获取图书信息 */
                "name": "EAGLE电路原理图与PCB设计方法及应用", /* 书名 */
                "deadline": "2015-05-07"                  /* 应还日期 */
            }
        ]
    }
   
####错误
错误时，NOA会返回error字段和错误提示。

	[
		"error" : "1",
		"msg"   : "",
	]	

错误信息

	Wrong parameter => 请求参数出错
	Wrong data      => 用户信息错误
	System error    => 网络延时或OPAC访问出错

---

###Renew 方法
通过Renew方法，进行续借操作。

	HTTP请求方式：POST
	http://nuptopenapi.pixelnfinite.com/library
	
####参数

	{
		"method"  : "renew",
		"student_id" : "",       /* 用户学号 */
		"password" : "",         /* OPAC密码 */
		"student_name" : "",     /* 若用户已认证，则此项可跳过，否则系统会自动认证 */
		"bar" : "",              /* 书籍条码 */
	}
	
####样例

	http://nuptopenapi.pixelnfinite.com/library?method=renew&student_id=B14010315&password=******&student_name=******&bar=1162846
		
####返回

    {
        "query": "success",
        "result": "",       /* failed 或 succeed */
    }
   
####错误
错误时，NOA会返回error字段和错误提示。

	[
		"error" : "1",
		"msg"   : "",
	]	

错误信息

	Wrong parameter => 请求参数出错
	Network error   => 网络延时或OPAC访问出错
	Wrong data      => 错误的用户账号
	Bar_code error  => 错误的条码

---

### History 方法
通过History方法，检索用户借阅历史。

    HTTP请求方式：POST
    http://nuptopenapi.pixelnfinite.com/library

####参数

    {
        "method"  : "history",
        "student_id" : "",       /* 用户学号 */
        "password" : "",         /* OPAC密码 */
        "student_name" : "",     /* 若用户已认证，则此项可跳过，否则系统会自动认证 */
    }

####样例

    http://nuptopenapi.pixelnfinite.com/library?method=history&student_id=B14010312&password=********&student_name=%E5%8D%9E%E8%BF%9C%E5%93%B2

####返回

    {
        "list": [
            {
                "bar": "1547970",                         /* 图书条码 */
                "marc_no": "0000455211",                  /* MARC码，用以获取图书信息 */
                "name": "EAGLE电路原理图与PCB设计方法及应用", /* 书名 */
                "borrowdate": "2015-05-07"                /* 借阅日期 */
                "returndate": "2015-05-07"                /* 归还日期 */
            }
        ]
    }

####错误
错误时，NOA会返回error字段和错误提示。

    [
        "error" : "1",
        "msg"   : "",
    ]

错误信息

    Wrong parameter => 请求参数出错
    Wrong data      => 用户信息错误
    System error    => 网络延时或OPAC访问出错

---

###Appointment 方法
通过Appointment方法进行图书预约。