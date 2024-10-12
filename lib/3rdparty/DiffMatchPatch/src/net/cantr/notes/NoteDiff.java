package net.cantr.notes;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.LinkedList;
import com.google.gson.Gson;

import name.fraser.neil.plaintext.diff_match_patch;
import name.fraser.neil.plaintext.diff_match_patch.Diff;

/**
 * Class whose main() reads json object from stdin, decodes it,
 * diffs "previous" and "current" versions of the text and
 * returns result as a json-encoded html text
 * @author Aleksander Chrabaszcz (GreeK)
 *
 */
public class NoteDiff {
	public static void main(String[] args) {
		
		// json object is read from stdin
		String json = readStdin();
		
		Gson gson = new Gson();
		NotesPair pair = gson.fromJson(json, NotesPair.class);
		
		// diff configuration
		diff_match_patch dmp = new diff_match_patch();
		dmp.Diff_Timeout = 5; // should work for no more than 5 seconds
		
		// perform diff
		LinkedList<Diff> diff_main = dmp.diff_main(pair.previous, pair.current);
		
		// print diff as json-encoded html text 
		String htmlOutput = printPrettyHtml(diff_main);
		String jsonString = gson.toJson(htmlOutput);
		
		// print result to stdout, to be read by php
		System.out.println(jsonString);
	}
	
	/**
	 * @return String from stdin
	 */
	private static String readStdin() {
		BufferedReader reader = new BufferedReader(
				new InputStreamReader(System.in));
		
		String line = null;
		StringBuilder sb = new StringBuilder();
		// read all lines to string builder
		try {
			while ((line = reader.readLine()) != null) {
				sb.append(line);
			}
		} catch (IOException e) {
			e.printStackTrace();
		}
		return sb.toString();
	}
	
	/**
	 * 
	 * Encodes diffs into string with encoded html special chars.
	 * Newline character is not encoded, which is different than 
	 * standard diff_match_patch.diff_pretty_html method
	 * Inserts and Deletes are encoded as &lt;span&gt;&lt;/span&gt; tags with css classes:
	 * "diffAdd" for added, "diffDel" for deleted parts
	 * 
	 * @param diffs got from diff_main method
	 * @return String html diff summary with encoded html special chars ('&', '<', '>').
	 */
	private static String printPrettyHtml(LinkedList<Diff> diffs) {
	    StringBuilder html = new StringBuilder();
	    for (Diff aDiff : diffs) {
	      String text = aDiff.text.replace("&", "&amp;").replace("<", "&lt;")
	          .replace(">", "&gt;");
	      switch (aDiff.operation) {
	      case INSERT:
	        html.append("<span class=\"diffAdd\">").append(text)
	            .append("</span>");
	        break;
	      case DELETE:
	        html.append("<span class=\"diffDel\">").append(text)
	            .append("</span>");
	        break;
	      case EQUAL:
	        html.append(text);
	        break;
	      }
	    }
	    return html.toString();
	}
}
