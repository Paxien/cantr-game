{strip}
    {$he} [$char_desc_is] <CANTR REPLACE NAME=char_desc_{$travel.type}> [$char_desc_in_direction] {$travel.direction}.
    {if !$lookingAtYourself}
      {if $travel.hisSpeed == 0}
        &nbsp;{$he} [$char_desc_has_stopped]
      {else}
        &nbsp;{$he} [$char_desc_is] <CANTR REPLACE NAME=char_desc_{$travel.type}>
        {if $travel.hisSpeed < $travel.ownSpeed}
          &nbsp;[$char_desc_slower_than_you]
        {elseif $travel.hisSpeed > $travel.ownSpeed}
          &nbsp;[$char_desc_faster_than_you]
        {else}
          &nbsp;[$char_desc_at_your_speed]
        {/if}
        {if $travel.difference == 0}
          , [$char_desc_next_to_you]
        {elseif $travel.difference > 0}
          , [$char_desc_ahead_of_you]
        {else}
          , [$char_desc_behind_to_you]
        {/if}
        {if $travel.isMoving}
          {if $travel.isSameDirection}
            , [$char_desc_facing_the_direction]
          {else}
            , [$char_desc_facing_the_opposite_direction]
          {/if}
        {/if}
      {/if}
      .
    {/if}
{/strip}
