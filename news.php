<?php
    include("includes/mysqli_conn.php");

    $result=$mysqli->query("SELECT * FROM `news` ORDER BY `date` LIMIT 10");

    if(isset($_POST["submit"])) {
        $search_phrase = $_POST["search_phrase"];

        $result=$mysqli->query("SELECT * FROM `news` 
        WHERE 
            `title` LIKE '%{$search_phrase}%' 
        OR
            `text` LIKE '%{$search_phrase}%' 
        ORDER BY `date` LIMIT 10");
    }



    $news = '';
    while($row=$result->fetch_assoc()) {
        $img = $row['img'];
        $url = $row['url'];
        $title = $row['title'];
        $text = mb_substr($row['text'], 0, 255).'...';
        $date = $row['date'];

        $news .= "<div class=\"card w-100 mb-3\">
                    <div class=\"row g-0\">
                        <div class=\"col-md-4\">
                            <a href=\"$url\" target=\"_blank\"><img src=\"$img\" class=\"card-img-top\" alt=\"$title\"></a>
                        </div>
                        <div class=\"col-md-8\">
                            <div class=\"card-body\">
                                <h5 class=\"card-title\">$title</h5>
                                <p class=\"card-text\">$text</p>
                                <a href=\"$url\" class=\"btn btn-primary\" target=\"_blank\">Открыть новость</a>
                            </div>
                        </div>
                    </div>
                </div>";
    }
?>

<!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="generator\" content="Hugo 0.88.1">
        <title>Blog Template · Bootstrap v5.1</title>
        <!-- Bootstrap core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3\" crossorigin="anonymous">
    </head>

    <body>
      <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Новости Оссетии</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Новости</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">О нас</a>
                        </li>
                    </ul>
                    <form class="d-flex" action="" method="post">
                        <input class="form-control mr-2" name="search_phrase" type="search" placeholder="Поиск новостей" aria-label="Search">
                        <button class="btn btn-outline-success" name="submit" type="submit">Найти</button>
                    </form>
                </div>
            </div>
        </nav>
      </header>
      <main class="mt-3">
          <div class="container">
            <div class="card-deck">
              <?=$news?>
            </div>
          </div>
      </main>
    </body>
