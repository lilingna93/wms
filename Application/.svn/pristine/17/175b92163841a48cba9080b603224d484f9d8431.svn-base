// console.log({timestamp: new Date().toLocaleString(),success:true});
/*
1、字符类

[abc] // 表示a 或者b 或者c
[^abc] // ^在这里表示取反，除了a、b、c之外的所有字符
[a-z] //-表示连接,从a到z的任意字符

\w // 等价于[a-zA-Z0-9_]，大小写字母、数字、下划线63字符中任意一个
\W // 等价于[^a-zA-Z0-9_]
\s // 任何Unicode空白符
\S // 任何Unicode非空白符
\d // 等价于[0-9]
\D // 等价于[^0-9]


2、重复
{n, m} // 最少重复n次，最多重复m次
{n, } // 至少重复n次
{n} //重复n次
? // 等价于 {0, 1}
+ // 等价于 {1,}
* // 等价于 {0,}
var reg = new RegExp('a{2,}');
var str = 'aaa';
var str2 = 'a';
reg.test(str); // true
reg.test(str2); // false


3、指定匹配位置
^ // 字符串开始位置（在字符类中表示取反）
$ // 字符串的结束位置

/^javascript/ // 字符串以javascript开始
/javascript$/ // 字符串以javascript结束

\b // 单词边界,也就是\w与\W的边界
var reg = /\bjava\b/;
var str1 = 'java';
var str2 ='javascript';
var str3 = 'java c++';
var str4 = 'html java c++';
reg.test(str1); // true
reg.test(str2); // false
reg.test(str3); // true
reg.test(str4); // true
在这里 \b 匹配非\w字符，包括字符串起始与结束。
\B与之相反,匹配非单词边界处


(?=p) // 要求字符串与p匹配，但是结果集并不包含匹配p的字符
var reg = /java(?=script)/;
var str = 'java';
var str1 = 'javascript';
reg.exec(str); // 匹配失败 ， 因为不包含script
reg.exec(str1); // 此时匹配成公，但是匹配结果并不包含script


(?!p) // 要求字符串不与p匹配
var reg = /java(?!script)/;
var str = 'javaee';
var str1 = 'javascript';
reg.exec(str); // 匹配成功，匹配结果为java
reg.exec(str1); // 匹配失败,因为包含script

4、修饰符
i // 不区分大小写
m // 匹配多行（使用^ $指定起止时候能通融\n换行)
g // 匹配成功第一处，并不会继续停止，会继续寻找所有匹配
s //允许.去匹配多行

*/

/*
let str = "2018-10-18";
let regex = /^(\d{4})\D(\d{2})\D(\d{2})$/;
console.log(str.match(regex));//[ '2018-10-18', '2018', '10', '18', index: 0, input: '2018-10-18' ]
*/

/*
let str = "2018-10-18";
let regex = /^(\d{4})\D(\d{2})\D(\d{2})$/;
console.log(regex.exec(str));//[ '2018-10-18', '2018', '10', '18', index: 0, input: '2018-10-18' ]
*/

/*
let str = "2018-10-18";
let regex = /^(\d{4})\D(\d{2})\D(\d{2})$/;
regex.test(str);
console.log(RegExp.$1, RegExp.$2, RegExp.$3); //2018 10 18
*/

/*
let str = "2018-10-18";
let regex = /^(\d{4})\D(\d{2})\D(\d{2})$/;
let date = [];
str.replace(regex, function(match, year, month, day) {
    date.push(year, month, day);
});
console.log(date);//[ '2018', '10', '18' ]
*/

/*
匹配时间

分析:
共 4 位数字，第一位数字可以为 [0-2]。
当第 1 位为 "2" 时，第 2 位可以为 [0-3]，其他情况时，第 2 位为 [0-9]。
第 3 位数字为 [0-5]，第4位为 [0-9]。
  */

/*
var regex = /^([01][0-9]|[2][0-3]):[0-5][0-9]$/;
  console.log( regex.test("23:59") );
  console.log( regex.test("02:07") );
  */



/*匹配有效数字

有效数字可以是正数、负数、零、小数，所以其特点为：

"."可以出现也可以不出现，一旦出现，后面必须跟着一位或多为数字；

最开始可能有“+/-”，也可以没有；

整数部分，一位数的情况可以是0-9中的一个，多位数的情况下不能以0开头*/

/*
var reg = /^[+-]?(\d|([1-9]\d+))(\.\d+)?$/;
console.log(reg.test('0.001'))
*/



/*
年龄介于18-65之间*/
/*
var reg = /^1[8-9]|[2-5]\d|6[0-5]$/;
console.log(reg.test('18'));
*/


var reg = /\s/;
console.log(reg.test('n d'));
