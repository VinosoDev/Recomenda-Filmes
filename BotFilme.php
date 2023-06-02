<?php
// Chave da API de TMDB
$api_key = "31aa57da784292d1c64c2efb4888da4e";

// Função para pesquisar filmes no TMDB
function pesquisarFilmes($genre, $releaseYear = "", $durationFilter = "")
{
    global $api_key;

    // Montar a URL da API
    $url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=pt-BR&sort_by=popularity.desc&with_genres=$genre&include_adult=false&include_video=false&page=1&vote_count.gte=1000";

    // Verificar se um ano específico foi fornecido
    if ($releaseYear !== "qualquer" && !empty($releaseYear)) {
        $url .= "&primary_release_year=$releaseYear";
    }

    if ($durationFilter === "short") {
        $url .= "&with_runtime.lte=90";
    } elseif ($durationFilter === "long") {
        $url .= "&with_runtime.gte=90";
    }

    // Fazer a requisição à API
    $all_results = array();

    $page = 1;
    while (count($all_results) < 56) {
        $page_url = $url . "&page=$page";

        // Fazer a requisição à API
        $response = file_get_contents($page_url);

        // Decodificar a resposta JSON
        $data = json_decode($response, true);

        $results = $data["results"];

        if (empty($results)) {
            break; // Sai do loop se não houver mais resultados
        }

        foreach ($results as $result) {
            $poster_path = "https://image.tmdb.org/t/p/w500" . $result["poster_path"]; // Obter a URL completa do pôster
            $all_results[] = array(
                "id" => $result["id"],
                "title" => $result["title"],
                "release_date" => $result["release_date"],
                "poster_path" => $poster_path, // Usar a URL do pôster atualizada
            );

            if (count($all_results) === 56) {
                break 2; // Sai do loop externo se o limite de 56 filmes for atingido
            }
        }

        $page++;
    }

    return $all_results;
}

// Verificar se a mensagem contém uma ação
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["genre"]) && isset($_GET["release_year"]) && isset($_GET["duration_filter"])) {
    $genre = $_GET["genre"];
    $releaseYear = $_GET["release_year"];
    $durationFilter = $_GET["duration_filter"];

    // Pesquisar filmes com base nos filtros
    $movie_list = pesquisarFilmes($genre, $releaseYear, $durationFilter);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chatbot de Recomendação de Filmes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #1f1f1f;
            color: #ffffff; /* Define a cor do texto como branco */
        }

        h1 {
            margin-top: 30px;
            color: #ffffff; /* Define a cor do título como branco */
        }

        form {
            width: 400px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #ffffff;
        }

        select {
            background-color: #ffffff;
            width: 100%;
            padding: 5px;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #8b0000;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .recommendation {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }

        .movie-card {
            width: 200px;
            margin: 10px;
            background-color: #ffffff;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .movie-card img {
            width: 100%;
            height: auto;
        }

        .movie-info {
            padding: 10px;
        }

        .movie-title {
            font-size: 12px;
            text-decoration-color: lightgray;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000000;
        }

        .movie-year {
            font-size: 10px;
            text-decoration-color: lightgray;
            font-weight: bold;
            color: #000000;
        }
    </style>

</head>

<body>
    <h1>Chatbot de Recomendação de Filmes</h1>
    <form method="GET" action="">
        <label for="genre">Gênero:</label>
        <select name="genre" id="genre">
            <option value="28">Ação</option>
            <option value="16">Animação</option>
            <option value="12">Aventura</option>
            <option value="35">Comédia</option>
            <option value="80">Crime</option>
            <option value="18">Drama</option>
            <option value="10751">Família</option>
            <option value="14">Fantasia</option>
            <option value="36">História</option>
            <option value="27">Terror</option>
            <option value="10402">Música</option>
            <option value="10749">Romance</option>
            <option value="878">Ficção Científica</option>
            <option value="10752">Guerra</option>
            <option value="37">Faroeste</option>
        </select>

        <label for="release_year">Ano de Lançamento:</label>
        <select name="release_year" id="release_year">
            <option value="qualquer">Qualquer Data</option>
            <?php
            $currentYear = date("Y");
            for ($year = $currentYear; $year >= 2000; $year--) {
                echo "<option value=\"$year\">$year</option>";
            }
            ?>
        </select>

        <label for="duration_filter">Duração:</label>
        <select name="duration_filter" id="duration_filter">
            <option value="">Qualquer Duração</option>
            <option value="short">(menos de 90 minutos)</option>
            <option value="long">(90 minutos ou mais)</option>
        </select>

        <input type="submit" value="Buscar">
    </form>

    <?php
    if (isset($movie_list)) {
        if (count($movie_list) > 0) {
            echo "<div class=\"recommendation\">";
            foreach ($movie_list as $movie) {
                echo "<div class=\"movie-card\">";
                echo "<img src=\"" . $movie["poster_path"] . "\">";
                echo "<div class=\"movie-info\">";
                echo "<div class=\"movie-title\">" . $movie["title"] . "</div>";
                echo "<div class=\"movie-year\">" . $movie["release_date"] . "</div>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>Não foi possível encontrar filmes com base nos filtros selecionados.</p>";
            
            // Recomendações baseadas no gênero selecionado
            $genreRecomendations = pesquisarFilmes($genre);
            if (count($genreRecomendations) > 0) {
                echo "<h2>Recomendações com base no gênero:</h2>";
                echo "<div class=\"recommendation\">";
                foreach ($genreRecomendations as $movie) {
                    echo "<div class=\"movie-card\">";
                    echo "<img src=\"" . $movie["poster_path"] . "\">";
                    echo "<div class=\"movie-info\">";
                    echo "<div class=\"movie-title\">" . $movie["title"] . "</div>";
                    echo "<div class=\"movie-year\">" . $movie["release_date"] . "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<p>Não foi possível encontrar recomendações com base no gênero selecionado.</p>";
            }
        }
    }
    ?>
</body>

</html>
