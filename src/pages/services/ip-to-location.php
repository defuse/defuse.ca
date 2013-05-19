<?php
    function loginToIpLocationDB()
    {
        $result = FALSE;
        try {
            $creds = Creds::getCredentials("ip2loc");
            $result = new PDO(
                "mysql:host={$creds[C_HOST]};dbname={$creds[C_DATB]}",
                $creds[C_USER], // Username
                $creds[C_PASS], // Password
                array(PDO::ATTR_PERSISTENT => true)
            );
            unset($creds);
        } catch(Exception $e) {
            $result = FALSE;
        }

        return $result;
    }

    $failure = FALSE;
    $success = FALSE;
    $ip_text = FALSE;

    if(isset($_GET['ip']))
    {
        $IPDB = loginToIpLocationDB();

        if($IPDB == FALSE)
        {
            die('Cannot connect to database.');
        }

        $ip = explode(".", $_GET['ip'], 4);
        if(count($ip) == 4)
        {
            $ip[0] = (int)$ip[0];
            $ip[1] = (int)$ip[1];
            $ip[2] = (int)$ip[2];
            $ip[3] = (int)$ip[3];

            $ip_text = "{$ip[0]}.{$ip[1]}.{$ip[2]}.{$ip[3]}";
            
            if($ip[0] <= 255 && $ip[1] <= 255 && $ip[2] <= 255 && $ip[3] <= 255 &&
                $ip[0] >= 0 && $ip[1] >= 0 && $ip[2] >= 0 && $ip[3] >= 0)
            {
                $first = (int)$ip[0] + 0;
                $q = $IPDB->prepare("SELECT country, city FROM `ip4_{$first}` WHERE b=:b AND c=:c");
                $q->bindParam(':b', $ip[1], PDO::PARAM_INT);
                $q->bindParam(':c', $ip[2], PDO::PARAM_INT);
                $q->execute();

                $result = $q->fetch();
                if($result !== FALSE)
                {
                    $q = $IPDB->prepare("SELECT name FROM countries WHERE id=:countryid");
                    $q->bindParam(':countryid', $result['country'], PDO::PARAM_INT);
                    $q->execute();

                    $country = $q->fetchColumn();
                    $success = TRUE;

                    $city = FALSE;
                    if($result['city'] > 0)
                    {
                        $q = $IPDB->prepare("SELECT name FROM cityByCountry where city=:cityid");
                        $q->bindParam(':cityid', $result['city'], PDO::PARAM_INT);
                        $q->execute();
                        
                        $city = urldecode($q->fetchColumn());
                    }
                }
                else
                {
                    $failure = TRUE;
                }
            }
            else
            {
                $failure = TRUE;
            }
        }
        else
        {
            $failure = TRUE;
        }
    }
?>

<h1>IP Address to Location</h1>

<?php
    if($failure)
    {
?>
        <center>
        <p><b>Unknown or Invalid IP Address.</b></p>
        </center>
<?
    }
    elseif($success)
    {
        $country = ($country) ? $country : "Unknown.";
        $city = ($city) ? $city : "Unknown.";
        ?>
            <div style="margin: 0 auto; width: 500px; background-color: #84e9ff; padding: 5px; border: solid black 1px; margin-bottom: 20px;">
            <center>
            <b>IP <?php echo htmlspecialchars($ip_text, ENT_QUOTES); ?> Found!</b>
            <table>
            <tr>
                <th style="padding-right: 10px;">
                    Country: 
                </th>
                <td>
                    <?php echo htmlspecialchars($country, ENT_QUOTES); ?>
                </td>
            </td>
            <tr>
                <th style="padding-right: 10px;">
                    City: 
                </th>
                <td>
                    <?php echo htmlspecialchars($city, ENT_QUOTES); ?>
                </td>
            </td>
            </table>
            </center>
            </div>
        <?
    }

    $ip_text = ($ip_text) ? $ip_text : "";
?>

<form action="ip-to-location.htm" method="get">
<center>
    IP: <input type="text" name="ip" value="<?php echo htmlspecialchars($ip_text, ENT_QUOTES); ?>"/> 
    <input type="submit" value="Lookup.." />
</center>
</form>

<p>This is a simple IP address to location tool using the free <a href="http://hostip.info">HostIP.info IP Address to Location database</a>. Feel free to run automated queries against this page. I don't care as long as you don't use up all my bandwidth.</p>

