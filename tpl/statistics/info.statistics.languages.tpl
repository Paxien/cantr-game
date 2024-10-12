  <TR>
    <TD> {* NEEDED TO ACHIEVE BACKWARD COMPATIBILITY *}
      <TABLE BORDER="2" WIDTH="100%">
        <TR>
          <TD ALIGN="right"><B>[$page_statistics_header_ID]</B></TD>
          <TD><B>[$page_statistics_header_language]</B></TD>
          <TD ALIGN="right"><B>[$page_statistics_header_players]</B></TD>
          <TD ALIGN="right"><B>[$page_statistics_header_characters]</B></TD>
          <TD ALIGN="right"><B>[$page_statistics_header_spawning_locations]</B></TD>
          <TD ALIGN="right"><B>[$page_statistics_header_translated]</B></TD>
        </TR>
      
        {foreach from=$data key=lang_id item=lang_data}
          <TR>
            <TD ALIGN="right">{$lang_id}</TD>
            <TD>{$lang_data.name}</TD>
            <TD ALIGN="right">{$lang_data.players|default:0}</TD>
            <TD ALIGN="right"{if !$lang_data.spawn_allowed} style="color:#888888;"{/if}>{$lang_data.characters|default:0}</TD>
            <TD ALIGN="right"{if $lang_data.density_spawn} style="color:#888888;"{/if}>{$lang_data.spawn_locations|default:0}</TD>
            <TD ALIGN="right">{math equation="min(trans/all*100, 100)" trans=$lang_data.translated all=$base format="%.2f"} % </TD>
          </TR>
        {/foreach}
      </TABLE>
      <BR><BR>
      [$page_statistics_language_1]
      
    </TD> {* NEEDED TO ACHIEVE BACKWARD COMPATIBILITY *}
  </TR>