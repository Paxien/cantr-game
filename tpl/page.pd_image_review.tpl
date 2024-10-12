{include file="template.title.[$lang].tpl" title="[$title_review_images]"}
<div class="page">
  <table border>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Uploader</th>
      <th>Date</th>
      <th></th>
    </tr>
    {foreach $imgs as $img}
      <tr>
        <td>{$img->id}</td>
        <td>{$img->name}</td>
        <td><a href="index.php?page=infoplayer&player_id={$img->uploader_id}">{$img->uploader_id}</a></td>
        <td>{$img->date}</td>
        <td><a href="index.php?page=pdimagereview&img={$img->id}&accept=1">[accept]</a>
          <a href="index.php?page=pdimagereview&img={$img->id}&accept=2" onclick="return confirm('You are sure?');">[refuse]</a></td>
      </tr>
      <tr>
        <td colspan="5"><img style="margin-bottom:30px;" src="/pictures/getimg.php?img={$img->name}"/></td>
      </tr>
    {/foreach}
  </table>

  <div class="centered" style="margin-top:30px;">
    <a href="index.php?page=player"><img src="[$_IMAGES]/button_back2.gif"/></a>
  </div>
</div>
