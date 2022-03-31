<?php
/* Подключаемся к БД (имя сервера, имя пользователя БД, пароль БД, имя БД)*/
//$mysqli = new mysqli("localhost", "cc08668_osnews", "Pm21Pm21Pm21", "cc08668_osnews");
$mysqli = new mysqli("localhost", "root", "", "cc08668_osnews");

/* Получаем Xpath главной страницы */

parseWebsite("https://vladikavkaz-osetia.ru/news/");
outputNews();

function parseWebsite($url) {

  $mainPageXpath = getXpath($url);

  /* Находим все контейнеры div с классом item (карточки новостей) в контейнере div склассом news-list */
  foreach ($mainPageXpath->query("//div[contains(@class, 'news-list')]//a[contains(@class, 'item')]") as $item) {
    /* Находим DOM-элемент заголовка */
    $title = $mainPageXpath->query(".//div[@class='news-content']//h3", $item);
  
    /* Получаем ссылку на новость */
    $newsUrl = 'https://vladikavkaz-osetia.ru'.$item->getAttribute('href');
  
    /* Получаем Xpath новости */
    $newsText = null;
    $articleXpath = getXpath($newsUrl);
  
    /* Получаем дату */
    $date = $articleXpath->query("//div[@class='news-detail']//span[@class='news-date-time']");
    if($date[0] !== null) {
      $dateText = $date[0]->textContent;
      $newsDate = date("Y-m-d 00:00:00", strtotime($dateText));
    }
  
    /* Находим DOM-элемент изображения */
    $image = $articleXpath->query("//div[@class='news-detail']//img");
    /* Если элемент не пустой получаем значение атрибута src */
    
    if($image[0] !== null) {
      //Миниатюра 
      $imageFull = $image[0]->getAttribute('src');           // Ссылка на миниатюру
    }
  
    foreach($articleXpath->query("//div[@class='news-detail']") as $key => $articleElement) {
      $newsText .= strip_tags($articleElement->textContent."\n");
      /* Заменяем все \n \t на единичные экземпляры */
      $newsText = preg_replace("/(\r?\n){2,}/", "\n", $newsText);
      $newsText = preg_replace("/(\r?\t){2,}/", "\t", $newsText);
      /* Удаляем в конце в начале каждой новости ненужные \n, \t, , : */
      $newsText = trim($newsText, " \n\t:");
    }
  
    /* Получаем текстовое содержимое заголовка */
    $titleText = $title[0]->textContent;
  
    /* Добавляем новость в таблицу news*/
    $GLOBALS["mysqli"]->query("INSERT INTO `news`
    (`website_id`,`title`,`date`,`text`,`img`,`url`)
    VALUES (13, '{$titleText}', '{$newsDate}', '{$newsText}', '{$imageFull}','{$newsUrl}')");
  }
}

function getXpath($url) {
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
  /* Получаем исходный код страницы */
    $html = file_get_contents($url, false,  stream_context_create($arrContextOptions));
  /* Класс DOMDocument предназначен для работы с кодом HTML и XML */
    $doc = new DOMDocument();
  /* Загружаем html в класс */
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();
  /* Класс DOMXpath реализует язык запросов XPath к элементам XML-документа */
    $xpath = new DOMXpath($doc);

  return $xpath;
}

function outputNews() {
  $news = $GLOBALS["mysqli"]->query("SELECT * FROM `news`");

    echo "<!doctype html>
    <html lang=\"en\">
    <head>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <meta name=\"description\" content=\"\">
        <meta name=\"author\" content=\"Mark Otto, Jacob Thornton, and Bootstrap contributors\">
        <meta name=\"generator\" content=\"Hugo 0.88.1\">
        <title>Blog Template · Bootstrap v5.1</title>
        <!-- Bootstrap core CSS -->
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3\" crossorigin=\"anonymous\">
    </head>";

  echo "<body><div class=\"container\">";

  for ($i = 0; $i<count((array)$news)/3; $i+=3) {
    echo "<div class=\"row\">";
      for ($j = $i; $j<=$i+3; $j++) {
        $img = 'https://vladikavkaz-osetia.ru'.$news[$j]['img'];
        $href = $news[$j]['url'];
        $title = $news[$j]['title'];
        $text = mb_substr($news[$j]['text'], 0, 127).'...';
        echo "<div class=\"col-sm\"><div class=\"card\" style=\"width: 18rem;\">
                <img src=\"$img\" class=\"card-img-top\" alt=\"$title\">
                <div class=\"card-body\">
                  <h5 class=\"card-title\">$title</h5>
                  <p class=\"card-text\">$text</p>
                  <a href=\"$href\" class=\"btn btn-primary\">Открыть новость</a>
                </div>
              </div></div>";
      }
    echo "</div>";
  }

  // foreach($news as $article) {
  //   $img = 'https://vladikavkaz-osetia.ru'.$article['img'];
  //   $href = $article['url'];
  //   $title = $article['title'];
  //   $text = mb_substr($article['text'], 0, 127).'...';
  //   echo "<div class=\"col-sm\"><div class=\"card\" style=\"width: 18rem;\">
  //           <img src=\"$img\" class=\"card-img-top\" alt=\"$title\">
  //           <div class=\"card-body\">
  //             <h5 class=\"card-title\">$title</h5>
  //             <p class=\"card-text\">$text</p>
  //             <a href=\"$href\" class=\"btn btn-primary\">Открыть новость</a>
  //           </div>
  //         </div></div>";
  // }
  echo "</div></body>";
}
