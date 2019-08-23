<?php

header('Content-Type: text/html; charset=utf-8');
$maximum = 10;
$query_string = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$sort = isset($_REQUEST['rank']) && $_REQUEST['rank'] == 'pagerank' ? array(
    'sort' => 'pageRankFile desc') : false;
$results = false;
if ($query_string)
{
 
  require_once('solr-php-client/Apache/Solr/Service.php');
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample1');
  if (get_magic_quotes_gpc() == 1)
  {
    $query_string = stripslashes($query_string);
  }
  $additionalParameters = array(
    'fq' => 'a filtering query_string',
    'facet' => 'true',
    'facet.field' => array(
      'field_1',
      'field_2'
    ) 
  );
  
  
  try
  {

    if ($sort) 
      $results = $solr->search($query_string, 0, $maximum, $sort);
    else
      $results = $solr->search($query_string, 0, $maximum);
  }
  catch (Exception $e)
  {
    
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}
?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
  </head>
  <body style="background-color:#cef442;text-align:center;">
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query_string, ENT_QUOTES, 'utf-8'); ?>"/> <br>
      <input type="radio" name="rank" value="tf-idf" <?php if(isset($_REQUEST['rank']) && $_REQUEST['rank']=="tf-idf") echo "checked";?>>Lucene<br>
      <input type="radio" name="rank" value="pagerank" <?php if (isset($_REQUEST['rank']) && $_REQUEST['rank']=="pagerank") echo "checked";?>>page rank<br>
      <input type="submit"/>
    </form>
<?php

if ($results)
{
  
  
  $fileUrlMap = array();
  $file = fopen("URLtoHTML_guardian_news.csv","r");
  while(!feof($file)){
    $line = fgetcsv($file);
    $fileUrlMap[$line[0]] = $line[1];
  }
  fclose($file);   
  
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($maximum, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php

    $data = array();
    foreach ($doc as $field => $value)
    {
      if ($field == 'id' || $field == 'description' || $field == 'title' || $field == 'og_url')
      {
        $data[$field] = $value;
      }
    }
?>
          <tr>
            <th><?php echo htmlspecialchars('Title', ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php 
                  if (array_key_exists('og_url', $data)) 
                    echo "<a href='".htmlspecialchars($data['og_url'], ENT_NOQUOTES, 'utf-8')."'>".htmlspecialchars($data['title'], ENT_NOQUOTES, 'utf-8')."</a>";
                  else 
                    echo "<a href='".$fileUrlMap[explode("/",$data['id'])[8]]."'>".htmlspecialchars($data['title'], ENT_NOQUOTES, 'utf-8')."</a>";
                ?>
            </td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars('URL', ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php 
                  if (array_key_exists('og_url', $data)) 
                  {
                    echo "<a href='".htmlspecialchars($data['og_url'], ENT_NOQUOTES, 'utf-8')."'>".htmlspecialchars($data['og_url'], ENT_NOQUOTES, 'utf-8')."</a>";
                  }
                  else
                  {
                    $fileID = explode("/",$data['id'])[8];
                    echo "<a href='".$fileUrlMap[$fileID]."'>".$fileUrlMap[$fileID]."</a>";
                  }     
                ?>
            </td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars('ID', ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($data['id'], ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
          <tr>
            <th><?php echo htmlspecialchars('Description', ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php 
                  if (array_key_exists('description', $data)) 
                  {
                    echo htmlspecialchars($data['description'], ENT_NOQUOTES, 'utf-8');
                  }
                ?>
            </td>
          </tr>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>