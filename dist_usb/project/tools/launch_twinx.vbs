' Twinx ERP — Silent Launcher
' Runs launch_twinx.bat completely hidden (no console window)
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run Chr(34) & CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName) & "\launch_twinx.bat" & Chr(34), 0, False
