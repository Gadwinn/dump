<?php

// ini_set('precision', 16);

require __DIR__ . '/../vendor/autoload.php';

$null = null;
$bool = true;
$intM = -1;
$intS = 1;
$floatM = -1.2;
$floatS = 1.2;
$floatL = 1.234567891011121314151617181920;
$floatE = 12345678910.11121314151617181920;
$stringNumber = '1.23';
$string = 'string';
$stringAccent = "AIpsum,\run calcul complexe de bytes dans un texte peut sembler trivial au premier abord,\nmais en réalité,\r\nil faut considérer plusieurs facteurs. Lorsqu'il s'agit de mesurer la taille d'un document en bytes, il est important de comprendre que chaque caractère n'occupe pas nécessairement le même espace de stockage. Par exemple, les caractères ASCII standard utilisent un byte chacun, tandis que les lettres accentuées dans des langues comme le français peuvent nécessiter jusqu'à trois bytes lorsqu'elles sont encodées en UTF-8. Donc, pour calculer la taille totale d'un texte, on commence par analyser chaque caractère et son encodage correspondant.\n\nSupposons que vous ayez un texte constitué exclusivement de lettres sans accent ou symboles spéciaux. Dans ce cas, ajouter les caracteres similaires à A-Z et a-z sera simple : chaque lettre prendra 1 byte. Cependant, cela devient plus difficile dès qu'on introduit des accents comme é ou è qui prennent parfois plus d'espace.\n\nDans un document donné où l'expression écrite se déroule souvent dans une variété de styles et avec une gamme riche d'accents et de symboles typographiques, le nombre total de bytes exact change considérablement. Pour illustrer cela dans un exemple pratique : prenons une phrase commune \"Le chat est sur le tapis\". Cette phrase comprend 25 caractères et lorsqu'elle est enregistrée sous un format comme UTF-8 vous reçoivez également des résultats subtils à prendre en compte car chaque espace compte !\n\nD'autre part, si dans notre exemple nous intégrons quelques éléments supplémentaires tels que des signets ou formats divers (comme le gras ou l'italique), il faut aussi prendre ces codes supplémentaires en considération car ils occupent également leur propre espace mémoire même si la somme des characters reste inchangée.\n\nAu fur et à mesure qu’on élargit notre analyse pour inclure différents types d'encodages comme ISO-8859-1 ou Windows-1252 -peut aussi influencer cette taille totale du fichier-. Les appels nécessaires au traitement peuvent être conséquents ; il vaut donc mieux avoir une idée claire avant d’effectuer toutes ces mesures.\n\nAinsi pour conclure ce raisonnement sur le calcul des bytes contenus dans un texte : nous avons réussi à explorer non seulement comment les différents encodages affectent la taille totale mais aussi pourquoi chaque spécificité linguistique pourrait entraîner différentes approches lors du stockage digital d'informations textuelles.. En somme l'arithmétique forme une liaison incroyable entre mots écrits et puissance numérique environnante qui mérite vraiment notre attention afin d'apprécier pleinement l'ampleur culturelle enrichissante qui réside dessinée à travers nos propres communications numériques quotidiennes."; // "السلام علیکم ورحمة الله وبرکاته!"; // 'śťŕîñğ';
$array = [1, 2, 3];
$assoc = ['key' => 'value'];
$assocR = json_decode('{"key": {"subkey": ["subsubvalue", "subsubvalue2", ["subsubsubvalue1", {"subsubsubkey1": "subsubsubvalue1"}]], "subkey2": "subvalue2"}, "key2": "value2"}', true);
$json = json_encode($assocR);
$jsonSlashed = '{\"ttt\":\"uuuu\"}';
$jsonSlashedR = '{"json_slahed":"{\\\\\"key\\\\\":\\\\\"value\\\\\"}"}';
$serialized = serialize($assocR);
$object = new stdClass();
$object->key = 'value';
$object->key2 = 'value2';
$object2 = new stdClass();
$object2->subkey = ['subsubvalue', 'subsubvalue2', ['subsubsubvalue1', ['subsubsubkey1' => 'subsubsubvalue1']]];
$object->subObject = $object2;
$serializedObject = serialize($object);
$mixedObjectArray = json_decode('{"key": {"subkey": ["subsubvalue", "subsubvalue2", ["subsubsubvalue1", {"subsubsubkey1": "subsubsubvalue1"}]], "subkey2": "subvalue2", "subkey3": {"subsubkey1": {"subsubsubkey1": "subsubsubvalue1"}}}, "key2": "value2"}');
$resource = fopen('php://memory', 'r');
$resourceClosed = fopen('php://memory', 'r');
fclose($resourceClosed);
$callable = function ($argA, ?string $argB = 'default') {
    return 'callable';
};
$arrow = fn($argA, ?string $argB = 'default') => 'arrow';
$iterable = new ArrayIterator([1, 2, 3]);
$exception = new Exception('Error message', 123);
try {
    function getError()
    {
        (function () {
            new ThisClassDoesNotExist();
        })();
    }
    getError();
} catch (Throwable $th) {
    $exceptions = new JsonException('JSON error', 123, new Exception('Previous error', 456, $th));
}
$classAnonymous = new class {
    public $key = 'value';
};
$dumper = new G\Dump\Dumper();

echo '<!DOCTYPE html>'
    . '<html lang="en">'
    . '<head>'
    . '<meta charset="UTF-8">'
    . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
    . '<title>Tests</title>'
    . '</head>'
    . '<body style="'
    . 'background-color: rgb(10 10 10);'
    . 'color: rgb(245 245 245);'
    . '">';
echo '<h3>empty</h3>';
egdump();
echo '<h3>null</h3>';
egdump($null);
echo '<h3>bool</h3>';
egdump($bool);
echo '<h3>intM</h3>';
egdump($intM);
echo '<h3>intS</h3>';
egdump($intS);
echo '<h3>floatM</h3>';
egdump($floatM);
echo '<h3>floatS</h3>';
egdump($floatS);
echo '<h3>floatL</h3>';
egdump($floatL);
echo '<h3>floatE</h3>';
egdump($floatE);
echo '<h3>stringNumber</h3>';
egdump($stringNumber);
echo '<h3>string</h3>';
egdump($string);
echo '<h3>stringAccent</h3>';
egdump($stringAccent);
echo '<h3>array</h3>';
egdump($array);
echo '<h3>assoc</h3>';
egdump($assoc);
echo '<h3>assocR</h3>';
egdump($assocR);
echo '<h3>json</h3>';
egdump($json);
echo '<h3>jsonSlashed</h3>';
egdump($jsonSlashed);
echo '<h3>jsonSlashedR</h3>';
egdump($jsonSlashedR);
echo '<h3>serialized</h3>';
egdump($serialized);
echo '<h3>object</h3>';
egdump($object);
echo '<h3>serializedObject</h3>';
egdump($serializedObject);
echo '<h3>mixedObjectArray</h3>';
egdump($mixedObjectArray);
echo '<h3>resource</h3>';
egdump($resource);
echo '<h3>resourceClosed</h3>';
egdump($resourceClosed);
echo '<h3>callable</h3>';
egdump($callable);
echo '<h3>arrow</h3>';
egdump($arrow);
echo '<h3>iterable</h3>';
egdump($iterable);
echo '<h3>exception</h3>';
egdump($exception);
echo '<h3>exceptions</h3>';
egdump($exceptions);
echo '<h3>classAnonymous</h3>';
egdump($classAnonymous);
echo '<h3>dumper</h3>';
egdump($dumper);

/* */
echo '<h3>FULL</h3>';
$full = [
    $null,
    $bool,
    // $intM,
    $intS,
    // $floatM,
    $floatS,
    // $floatL,
    // $floatE,
    // $stringNumber,
    $string,
    $stringAccent,
    // $array,
    // $assoc,
    // $assocR,
    // $json,
    // $jsonSlashed,
    // $jsonSlashedR,
    // $serialized,
    // $object,
    // $serializedObject,
    // $resource,
    // $resourceClosed,
    // $callable,
    // $arrow,
    // $iterable,
    // $exceptions,
    // $dumper,
];
// $full[] = $dumper;
egdump(...$full);
echo '<div style="background-color: rgb(245 245 245); color: rgb(10 10 10); padding: 0.1rem;">';
egdump(...$full);
echo '</div>';
/**/

// test echo die
echo gdump('echo gdump');
egdump('egdump');
dgdump('dgdump');

echo '</body>'
    . '</html>';
