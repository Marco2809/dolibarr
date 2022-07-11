<?php

$where_condition = " ";
$cod_asset = isset($_GET['cod_asset']) ? $_GET['cod_asset'] : "";
$codice_mov = isset($_GET['codice_mov']) ? $_GET['codice_mov'] : "";
$termid = isset($_GET['TERMID']) ? $_GET['TERMID'] : "";
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : "";
if (!empty($codice_mov))
{
    $where_condition = " WHERE riferimento LIKE '" . $codice_mov . "'";
}
if (!empty($cod_asset))
{
    $where_condition .= " WHERE codice_asset LIKE '" . $cod_asset . "'";
}

if (!empty($cliente))
{
    $where_condition .= " WHERE cliente LIKE '" . $cliente . "'";
}

$obj_tracking = new myTracking($db);
$tracking = $obj_tracking->getTracking($where_condition);

$root = DOL_URL_ROOT;
print '<div class="fiche">';
print '<div class="tabs" data-type="horizontal" data-role="controlgroup">';
print '<a class="tabTitle">
<img border="0" title="" alt="" src=""' . $root . '"/theme/eldy/img/object_product.png">

</a>';
$path = DOL_URL_ROOT;


print '</div>';
/*
print '<div class="tabBar">';
print '<tbody>';
print '<table class="border" width="100%">';
print '<tr>';
print '<td>Codice asset</td>';
print '<td>' . $tracking[0]['codice_asset'] . '</td>';
print '</tr>';

print '<tr>';
print '<td>Etichetta</td>';
print '<td>' . $tracking[0]['etichetta'] . '</td>';
print '</tr>';

print '<tr>';
print '<td>Data creazione asset</td>';
print '<td>' . $tracking[0]['data'] . '</td>';
print '</tr>';

print '<tr>';
print '<td>Ora</td>';
print '<td>' . $tracking[0]['ora'] . '</td>';
print '</tr>';

print '</tbody>';
print '</table>';
print '</div>';
*/


//per ogni asset
print' <table class="noborder" width="100%">';
print'<tbody>';

print '<tr class="liste_titre">';
print "<td>Data</td>";
print "<td>Modificato dall'utente</td>";
//print "<td>Azione</td>";
print "<td>Codice HW</td>";
print "<td>Quantita</td>";
print "<td>Cliente</td>";
print "<td>Riferimento</td>";

print"</tr>";
for ($i = 0; $i < count($tracking); $i++)
{
    print "<tr>";
    print "<td>" . $tracking[$i]['data'] . " " . $tracking[$i]['ora'] . "</td>";
    print "<td>" . $user->login . "</td>";
    //print "<td>" . $tracking[$i]['azione'] . "</td>";

    print "<td>" . $tracking[$i]['codice_asset'] . "</td>";
    print "<td>" . $tracking[$i]['quantita'] . "</td>";

    $obj_cliente = new clientiZoccali($db);
    $obj_mag_dest = $obj_cliente->getCliente($tracking[$i]['cliente']);
    $magazzino_nome_dest = $obj_mag_dest[0]['INSEGNA'];

    print "<td>" . $magazzino_nome_dest . "</td>";

    $nome_ddt = $tracking[$i]['riferimento'] . ".pdf";
    $down_mag = DOL_URL_ROOT . '/product/ordini/' . $nome_ddt;
    $link = '<a href="' . $down_mag . '">' . $tracking[$i]['riferimento'] . '</a></td>';
    print "<td>".$link . "</td>";
}
