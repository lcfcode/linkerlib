@echo off
setx NLS_LANG "SIMPLIFIED CHINESE_CHINA.AL32UTF8"

::获取日期 将格式设置为：20110820
set dateVar=%date:~0,4%%date:~5,2%%date:~8,2%
::获取时间中的小时 将格式设置为：24小时制
set timeVar=%time:~0,2%
if /i %timeVar% LSS 10 (
set timeVar=0%time:~1,1%
)
::获取时间中的分、秒 将格式设置为：3220 ，表示 32分20秒
set timeVar=%timeVar%%time:~3,2%%time:~6,2%

git add .
git commit -m "feat: %dateVar%%timeVar%"
git push origin master

curl -XPOST -H'content-type:application/json' 'https://packagist.org/api/update-package?username=lcfdev&apiToken=rjOn0zE0XEgm8oxZ0aV_' -d'{"repository":{"url":"https://packagist.org/packages/lcfdev/linkerlib"}}'

::md .\bak%dateVar%%timeVar%

::pause