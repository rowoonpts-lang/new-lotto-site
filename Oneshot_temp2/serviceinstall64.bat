set JAVA_HOME=C:\Program Files\Java\jdk-13.0.2

set AGENT_HOME=E:\Oneshot
set SERVICE_NAME=Oneshot2

set AGENT_NAME=Oneshot2.jar
set AGENT_CONFIG=Oneshot2.conf

"%AGENT_HOME%\JavaService64.exe" -install "%SERVICE_NAME%" "%JAVA_HOME%\bin\server\jvm.dll" -Djava.class.path="%AGENT_HOME%\%AGENT_NAME%" -start com.agent.Start -params "%AGENT_HOME%\%AGENT_CONFIG%" -stop com.agent.Stop -current "%AGENT_HOME%" -auto

pause