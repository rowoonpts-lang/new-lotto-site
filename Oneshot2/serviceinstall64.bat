set JAVA_HOME=C:\Program Files\Java\jdk1.6.0_26

set AGENT_HOME=d:\Oneshot2
set SERVICE_NAME=Smtnt Oneshot2

set AGENT_NAME=Oneshot2.jar
set AGENT_CONFIG=Oneshot2.conf

"%AGENT_HOME%\JavaService64.exe" -install "%SERVICE_NAME%" "%JAVA_HOME%\jre\bin\server\jvm.dll" -Djava.class.path="%AGENT_HOME%\%AGENT_NAME%" -start com.agent.Start -params "%AGENT_HOME%\%AGENT_CONFIG%" -stop com.agent.Stop -current "%AGENT_HOME%" -auto