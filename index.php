<?php
/* Подключаемся к БД (имя сервера, имя пользователя БД, пароль БД, имя БД)*/
//$mysqli = new mysqli("localhost", "cc08668_osnews", "Pm21Pm21Pm21", "cc08668_osnews");
$mysqli = new mysqli("localhost", "root", "", "cc08668_osnews");

/* Получаем Xpath главной страницы */

/* Будем получать новости с первых 5 страниц */
for ($page = 1; $page <= 5; $page++) {

$mainPageXpath = getXpath("https://vladikavkaz-osetia.ru/news/?PAGEN_1=$page");

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
  $mysqli->query("INSERT INTO `news`
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
