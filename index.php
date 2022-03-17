<?php
/* Получаем исходный код страницы */
$html = file_get_contents('https://www.nosu.ru/');
/* Класс DOMDocument предназначен для работы с кодом HTML и XML */
$doc = new DOMDocument();
/* Загружаем html в класс */
$doc->loadHTML($html);
/* Класс DOMXpath реализует язык запросов XPath к элементам XML-документа */
$xpath = new DOMXpath($doc);

$img_full_path = './uploads/';
$img_tmb_path = './uploads/thumbnails/';

/* Находим все контейнеры div с классом item (карточки новостей) в контейнере div склассом news-list */
foreach ($xpath->query("//div[contains(@class, 'news-list')]//div[contains(@class, 'item')]") as $item) {
  /* Находим DOM-элемент заголовка */
  $title = $xpath->query(".//div[contains(@class, 'title')]//a", $item);
  /* Получаем текстовое содержимое заголовка */
  $title_text = $title[0]->textContent;
  /* Получаем дату новости */
  $date = $xpath->query(".//div[contains(@class, 'date')]", $item);
  $date_text = $date[0]->textContent;

  $news_txt='';
  $aa = $xpath->query(".//a", $item);
  if($aa[0] !== null) {
    $news_url = $aa[0]->getAttribute('href');
    
    $news_txt = getNewsText($news_url);
  }
  /* Находим DOM-элемент изображения */
  $image = $xpath->query(".//a//img", $item);
  /* Если элемент не пустой получаем значение атрибута src */
  
  if($image[0] !== null) {
    //Миниатюра 
    $image_tmb = $image[0]->getAttribute('src');               // Ссылка на миниатюру
    $image_tmb_binary = file_get_contents($image_tmb);         // Бинарный код изображения

    //Исходное изображение 
    $image_full = str_replace('-350x230', '', $image_tmb);  // Ссылка на исходное
    $image_full_binary = file_get_contents($image_full);    // Бинарный код изображения

    file_put_contents($img_tmb_path.getGUID()."_tmb".".jpg", $image_tmb_binary);
    file_put_contents($img_full_path.getGUID().".jpg", $image_full_binary);
  }

  echo "<h3>{$title_text}</h3>";
  echo "<date>{$date_text}<date>";
  echo "<h4>{$news_txt}</h4>";
  echo "<div><img src=\"{$image_tmb}\"></div>";
}

function getNewsText($url) {
  $news_html = file_get_contents($url);
  $html_doc = new DOMDocument();
  $html_doc->loadHTML($news_html);

  $news_xpath = new DOMXpath($html_doc);

  $text = '';


  foreach($news_xpath->query("//div[@class='content-block content-text']//p") as $item) {
    $text .= $item->textContent."\n";
  }

  $html_doc = null;
  return $text;
}

function getGUID(){
  if (function_exists('com_create_guid')){
      return com_create_guid();
  }
  else {
      mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45);// "-"
      $uuid = chr(123)// "{"
          .substr($charid, 0, 8).$hyphen
          .substr($charid, 8, 4).$hyphen
          .substr($charid,12, 4).$hyphen
          .substr($charid,16, 4).$hyphen
          .substr($charid,20,12)
          .chr(125);// "}"
      return $uuid;
  }
}
