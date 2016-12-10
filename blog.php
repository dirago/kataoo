<?php

date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr_FR');

/**
 * Class BlogLoader
 * chargeur de données pour implémenter le blog
 */
class BlogLoader {
    /**
     * BlogLoader constructor.
     * @param $url
     */
    public function __construct(String $url) {
        $this->url = $url;
        $this->loadedData = $this->loadFromJSON($this->url);
    }

    /**
     * loading data(array) from json file
     * @param String $path
     * @return array
     */
    public function loadFromJSON(String $path): array {
        $data = file_get_contents($path);
        return json_decode($data, true);
    }
}

/**
 * Class Author
 * description d'un rédacteur
 */
class Author {
    public $id;
    public $firstName;
    public $lastName;

    public function __construct(int $id, String $firstName, String $lastName) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * renvoie le nom complet : Bob Lee
     * @return String
     */
    function getName(): String {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * renvoie le initial du prénom et nom complet : B.Lee
     * @return String
     */
    function getShortName(): String {
        return $this->firstName[0] . '.' . $this->lastName;
    }

    /**
     * renvoie les initiales : B.L
     * @return String
     */
    function getInitial(): String {
        return $this->firstName[0] . '.' . $this->lastName[0];
    }
}

/**
 * Class Article
 */
class Article {
    public $id;
    public $title;
    public $content;
    public $author;
    public $publicationDate;

    public function __construct(int $id, String $title, String $content, Author $author, DateTime $date) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->author = $author;
        $this->publicationDate = $date->format('d/m/Y');
    }
}


class ArticleRenderer {
    public $article;
    public $renderedArticle;

    public function __construct(Article $article) {
        $this->article = $article;
        echo $this->render();
    }

    /**
     * renvoie l'article mis en forme
     * <h2>titre</h2>
     * <p>article</p>
     * <p>par nom-court, le date </p>
     * @return void
     */
    function render(): String {
        $this->renderedArticle .= '<p class="retour"><a href="./blog.php">RETOUR</a></p>';
        $this->renderedArticle .= '<h2 class="titre">' . $this->article->title . '</h2>';
        $this->renderedArticle .= '<p class="contenu">' . $this->article->content . '</p>';
        $this->renderedArticle .= '<p class="signature">par ' . $this->article->author->getShortName() . ', le ' . $this->article->publicationDate . '</p>';
        return $this->renderedArticle;
    }
}

class Blog {
    public $title;
    public $data;
    public $authors;
    private $renderer;

    public function __construct(String $title, array $articles) {
        $this->title = $title;
        $this->data = $articles;
        $this->authors = $this->getAuthors($this->data);
        $this->articles = $this->getArticles($this->data);
    }

    /**
     * Renvoie le header du blog
     * <header>titre
     * @return String
     */
    function displayHeader(): String {
        return '<header>' . $this->title . '</header>';
    }

    /**
     * affiche la liste des titres d'articles sous formes de liens vers les articles
     */
    function displayArticleList(): String {
        $renderedArticlesList = '';
        foreach ($this->articles as $article) {
            $renderedArticlesList .= '<p><a class="preview" href="?articleId=' . $article->id . '">' . $article->content . '</a></p>';
        }
        return $renderedArticlesList;
    }

    /**
     * renvoie le contenu HTML d'un article
     * @param int $articleId
     * @return String
     */
    function displayArticle(int $articleId) {
        foreach ($this->articles as $article) {
            if ($articleId == $article->id) {
                $this->renderer = new articleRenderer($article);
            }
        }
    }

    /**
     * renvoie un footer avec la date du jour
     * <footer></footer>
     */
    function displayFooter() {
        return "
            <footer>
                <p>Nous sommes le " . strftime("%A %d %B %Y") . "</p>
                <p>Simplon.co Lyon >>> <a href='https://github.com/dirago'>dirago</a></p>
            </footer>
            ";
    }

    /**
     * extracting authors array from original array
     * @param array $array
     * @return array
     */
    public function getAuthors(array $array): array {
        if (isset($array['authors'])) {
            $authors = $array['authors'];
            $authorsArray = Array();
            foreach ($authors as $author) {
                array_push($authorsArray, new Author($author['id'], $author['firstname'], $author['lastname']));
            }
            return $authorsArray;
        }
    }

    /**
     * extracting articles array from original array
     * @param array $array
     * @return array of articles
     */
    public function getArticles(array $array): array {
        if (isset($array['articles'])) {
            $articlesArray = Array();
            foreach ($array['articles'] as $article) {
                foreach ($this->authors as $author) {
                    if ($article['authorId'] == $author->id) {
                        $articleAuthor = $author;
                    }
                }
                $newArticle = new Article($article['articleId'], $article['title'], $article['content'], $articleAuthor, new DateTime($article['date']));
                array_push($articlesArray, $newArticle);
            }
            return $articlesArray;
        }
    }
}

// et pourquoi pas essayer de trouver 2/3 trucs à mettre dans un "helper"
class ViewHelper {

}

$blogLoader = new BlogLoader('blog.json');
$articles = $blogLoader->loadedData;
$blog = new Blog('Vive la POO', $articles);

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $blog->title ?></title>
    <link href="https://fonts.googleapis.com/css?family=Dosis:400,700|Nunito+Sans:300,400" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body>
<?= $blog->displayHeader(); ?>
<main>
    <?= !isset($_GET['articleId']) ? $blog->displayArticleList() : $blog->displayArticle($_GET['articleId']); ?>
</main>
<?= $blog->displayFooter(); ?>
</body>
</html>
