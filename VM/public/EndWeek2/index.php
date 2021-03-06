<html>
    <head>
        <style>
	    body{
	        font-family: Arial, Helvetica, sans-serif;
	    }
	    table{
	        font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif; 
	        font-size: 12px;    
	        margin: 45px;     
	        width: 480px; 
	        border-collapse: collapse; 
	    }
	    th{
	        font-size: 13px;     
	        font-weight: normal;     
	        padding: 8px;     
	        background: #b9c9fe; 
	        border-top: 4px solid #aabcfe;    
	        border-bottom: 1px solid #fff; 
	        color: #039; 
	    }
	    td{
	        padding: 8px;     
	        background: #e8edff;     
	        border-bottom: 1px solid #fff; 
	        color: #669;    
	        border-top: 1px solid transparent; 
	    }
	    tr:hover td{ 
	        background: #d0dafd; 
	        color: #339; 
	    }
	    h1{
	        color:red;
	    }
	    h2{
	        color:blue;
	    }
	    input{
	        width:375px;
	        display:block;
	        border: 1px solid #999;
	        height: 35px;
	        -webkit-box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
	        -moz-box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
	        box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
	    }
	    #button {
	        margin:20px;
	        width:100px;
	        background:#09C;
	        color:#fff;
	        font-family: Tahoma, Geneva, sans-serif;
	        height:30px;
	        -webkit-border-radius: 15px;
	        -moz-border-radius: 15px;
	        border-radius: 15px;
	        border: 1p solid #999;
	    }
        </style>
        <meta charset="utf-8" />
    </head>
<html>
<?php
    require_once __DIR__.'/vendor/autoload.php'; 
    use Silex\Provider\FormServiceProvider;
    use Silex\Provider\TwigServiceProvider;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Validator\Constraints as Assert;
    
    $app = new Silex\Application(); 
    $app->register(new Silex\Provider\FormServiceProvider());
    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__ . '/views',
        'twig.class_path' => __DIR__ . '/../vendor/twig/twig/lib',
    ));
    $app->register(new Silex\Provider\SwiftmailerServiceProvider());
    $app->register(new Silex\Provider\ValidatorServiceProvider());
    $app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.domains' => array(),
    ));
	
    $app->get('/', function () {
        echo "<center><H1>Books</H1>";
        echo "<h2><a href=\"index.php/search/\">Search</a>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<a href=\"index.php/new/\">New</a></h2></center>";
        include 'database.php';
        return "";
    });
	
    $app->get('/libro/{ident}/', function($ident){
        echo "<center><H1>Book</H1>";
        include 'dbcon.php';
        while($row = $STH->fetch()) {
            if($row['id']==$ident){
                echo "<center><table>";
                echo "<th>".$row['title']."</th>";
                echo "<tr><td>Price: ".$row['price']."</td></tr>";
                echo "<tr><td>".$row['description']."</td></tr>";
                echo "<tr><td>";
                echo "<a href=\"change/\">Modify</a>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo "<a href=\"delete/\">Delete</a>";
                echo "</td></tr>";
                echo "</table>";
                echo "</center>";
            }
        }
        return "<a href=\"/bootcamp/wtwo/\">Home</a>";
    });

    $app->get('/libro/{ident}/delete/', function($ident){
        $link = mysql_connect('localhost', 'root', '') or die('Can not connect to server: ' . mysql_error());
        mysql_select_db('books') or die('Can not select the database.');
        mysql_query("SET NAMES 'utf8'");
        $query="DELETE FROM `books` WHERE `books`.`id` = $ident";
        $result = mysql_query($query) or die('Error!: ' . mysql_error());
        if($result){
            echo "Eliminado.";
            echo "<br>";
            return "<a href=\"/bootcamp/wtwo/\">Home</a>";
        }
        mysql_close($link);
    });

    $app->match('/libro/{ident}/change/', function(Request $request,$ident) use ($app) {
        $sent = false;
        /*include 'dbcon.php';
        while($row = $STH->fetch()){
            if($row['id']=$ident){
                $default = array(
                    'id' => ''.$row['id'].'',
                    'title' => ''.$row['title'].'',
                    'price' => ''.$row['price'].'',
                    'description' => ''.$row['description'].'',
                );
            }
        }
        $form = $app['form.factory']->createBuilder('form', $default)
            ->add('title')
            ->add('price')
            ->add('description')
            ->getForm();
        $form->handleRequest($request);*/
        $link = mysql_connect('localhost', 'root', '') or die('Can not connect to server: ' . mysql_error());
        mysql_select_db('books') or die('Can not select the data base.');
        mysql_query("SET NAMES 'utf8'");
        $query=mysql_query("SELECT * FROM `books` WHERE `id`=$ident");
        $default=mysql_fetch_row($query);
        $default= array(
            'id'=>$default[0],
            'title'=>$default[1],
            'price'=>$default[2],
            'description'=>$default[3]
        );
        $form = $app['form.factory']->createBuilder('form', $default)
            ->add('title')
            ->add('price')
            ->add('description')
            ->getForm();
        $form->handleRequest($request);
        
        if ('POST' == $request->getMethod()) {
            if ($form->isValid()) {
                $default = $form->getData();
                $sid=$default['id'];
                $stitle=mysql_real_escape_string($default['title']);
                $sprice=filter_var($default['price'], FILTER_VALIDATE_FLOAT);
                $sdesc=mysql_real_escape_string($default['description']);
                $link = mysql_connect('localhost', 'root', '') or die('Can not connect to server: ' . mysql_error());
                mysql_select_db('books') or die('Can not select the data base.');
                mysql_query("SET NAMES 'utf8'");
                $query="UPDATE `books` SET `title` = '$stitle', `price` = '$sprice', `description` = '$sdesc' WHERE `books`.`id` = '$sid'";
                $result = mysql_query($query) or die('Error!: ' . mysql_error());
                if($result){
                    echo "Ready.";
                    echo "<br>";
                    return "<a href=\"/bootcamp/wtwo/\">Home</a>";
                }
                mysql_close($link);
            }
        }
        return $app['twig']->render('index.twig', array('form' => $form->createView()));
        echo "<br>";
    });
    $app->match('/new/', function(Request $request) use ($app) {
        $sent = false;
        $default = array(
            'title' => 'Title',
            'price' => '00',
            'description' => 'Description',
        );
        $form = $app['form.factory']->createBuilder('form', $default)
            ->add('title')
            ->add('price')
            ->add('description')
            ->getForm();
        $form->handleRequest($request);
        
        if ('POST' == $request->getMethod()) {
            if ($form->isValid()) {
                $default = $form->getData();
                $stitle=mysql_real_escape_string($default['title']);
                $sprice=filter_var($default['price'], FILTER_VALIDATE_FLOAT);
                $sdesc=mysql_real_escape_string($default['description']);
                $link = mysql_connect('localhost', 'root', '') or die('Can not connect to server: ' . mysql_error());
                mysql_select_db('books') or die('Can not select the data base.');
                mysql_query("SET NAMES 'utf8'");
                $query="INSERT INTO `books`(`title`,`price`,`description`) VALUES ('$stitle','$sprice','$sdesc')";
                $result = mysql_query($query) or die('Consulta fallida: ' . mysql_error());
                if($result){
                    echo "Ready.";
                    echo "<br>";
                    return "<a href=\"/bootcamp/wtwo/\">Home</a>";
                }
                mysql_close($link);
            }
        }
        return $app['twig']->render('index.twig', array('form' => $form->createView()));
    });

    $app->match('/search/', function(Request $request) use ($app) {
        $sent = false;
        $default = array(
            'search' => 'Title or Description',
        );
        $form = $app['form.factory']->createBuilder('form', $default)
            ->add('search')
            ->getForm();
        $form->handleRequest($request);
        if ('POST' == $request->getMethod()) {
            if ($form->isValid()) {
                $default = $form->getData();
                $search=mysql_real_escape_string($default['search']);
                $host="localhost"; $dbname="books"; $user="root"; $pass="";
                $DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
                $STH = $DBH->query("SELECT `id`, `title` FROM `books` WHERE `title` LIKE '%$search%' or `description` LIKE '%$search%'");
                $STH->setFetchMode(PDO::FETCH_ASSOC);
                echo "<center><h1>Books</h1><table>";
                echo "<th align=center>Titles</th>";
                while($row = $STH->fetch()) {
                    echo "<tr><td align=center>";
                    echo "<a href=\"/bootcamp/wtwo/index.php/libro/".$row['id']."\">".$row['title']."</a>";
                    echo "</td></tr>";
                }
                echo "</table></center>";
                return "<a href=\"/bootcamp/wtwo/\">Home</a>";
            }
        }
        return $app['twig']->render('index.twig', array('form' => $form->createView()));
    });
    $app['debug'] = true;
    $app->run();
?>
