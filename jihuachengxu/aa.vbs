Dim WshShell
Set WshShell = CreateObject("WScript.Shell")
Do While True
     WScript.Sleep 1000*30       '�ȴ�60��
     WshShell.Run "C:\Intel\cron.bat",0,True
Loop