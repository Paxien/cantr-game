It's a java implementation of diff_match_patch as a .java file + my own class NoteDiff.java
It reads json object with two versions of text from stdin, performs diff and writes json encoded html string with diff to stdout.
Java is used instead of php, because php implementation of diff_match_patch or any other diff-by-character library was too slow for some notes.
Jar file in bin/ is compiled from a project made of code from src/ + external library - gson (in lib/)

Made by Aleksander Chrabaszcz (GreeK) 17-01-2014