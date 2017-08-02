##NUPT Open API ZF 模块
ZF模块提供正方教育系统接口，通过ZF可以访问GPA、课表、空教室及检验身份。

_由于正方服务器限制，请求速度较慢。_

###方法列表

* Login
* Info
* Score
* EmptyClassroom

---
### Login方法
通过Login方法，登录正方服务器，验证用户身份。请求正确则返回姓名

    HTTP请求方式：GET
    http://nuptopenapi.pixelnfinite.com/zf

####参数

    {
        "method"  : "login",
        "student_id" : "",        /* 用户学号 */
        "student_password" : "",  /* 用户姓名 */
    }

####样例

    http://nuptopenapi.pixelnfinite.com/zf?method=login&student_id=B14010312&student_password=******

####返回

    {
        "student_name": "XXX",
    }

---
### Info方法
通过Info方法，获取用户信息

    HTTP请求方式：GET
    http://nuptopenapi.pixelnfinite.com/zf

####参数

    {
        "method"  : "info",
        "student_id" : "",        /* 用户学号 */
        "student_password" : "",  /* 用户姓名 */
    }

####样例

    http://nuptopenapi.pixelnfinite.com/zf?method=info&student_id=B14010312&student_password=******

####返回
    {
        "student_idcard": "3201021995...",        /* 身份证号 */
        "student_name": "卞远哲",                  /* 姓名 */
        "student_mobile": "1866120...",           /* 手机号码 */
        "student_tel": "186612...",               /* 联系方式 */
        "student_email": "48928791...",           /* 邮箱 */
        "student_sex": "男",                      /* 性别 */
        "student_depatment": "通信与信息工程学院",   /* 学院 */
        "student_major": "通信工程"                /* 专业 */
    }

---
####Score方法
通过Score方法，获取学生在校成绩

    HTTP请求方式：GET
    http://nuptopenapi.pixelnfinite.com/zf

####参数

    {
        "method"  : "score",
        "student_id" : "",        /* 用户学号 */
        "student_password" : "",  /* 用户姓名 */
    }

####样例

    http://nuptopenapi.pixelnfinite.com/zf?method=score&student_id=B14010312&student_password=******

####返回

    {
        "gpa": "3.59",
        "detail": [
            {
                "学年": "2014-2015",
                "学期": "1",
                "课程代码": "B3500011S",
                "课程名称": "大学生心理健康",
                "课程性质": "必修",
                "课程归属": " ",
                "学分": "0.5",
                "绩点": "3.70",
                "成绩": "87",
                "辅修标记": "0",
                "补考成绩": " ",
                "重修成绩": " ",
                "学院名称": "教育科学与技术学院",
                "备注": " ",
                "重修标记": "0",
                "课程英文名称": ""
            },
            ...
        ]
    }

---
### 错误
请见[NUPTOpenAPI 全局错误返回码](http://document.pixelnfinite.com/page!13)