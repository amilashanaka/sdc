<?php


if (isset($_SESSION['message'])) {
    $message = is_array($_SESSION['message']['text']) ? implode(" ", $_SESSION['message']['text']) : $_SESSION['message']['text'];
    echo '<script>
        swal({
            title: "' . $_SESSION['message']['title'] . '",
            text: "' .  $message . '",
            icon: "' . $_SESSION['message']['icon'] . '"
                });
      </script>';
     
    unset($_SESSION['message']);
}

if (isset($_SESSION['error']) && $_SESSION['error']!='No data found') {
    echo '<script>
        swal({
            title: "Error",
            text: "' . $_SESSION['error'] . '",
            icon: "error"
        });
      </script>';
     
    unset($_SESSION['error']);
}


if (isset($_SESSION['custom'])) {
    echo '<script>
        swal({
            title: "Error",
            text: "' . $_SESSION['custom'] . '",
            icon: "error"
        });
      </script>';
     
    unset($_SESSION['custom']);
}

