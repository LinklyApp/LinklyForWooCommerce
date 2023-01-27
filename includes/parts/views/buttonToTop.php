<script>
    document.body.onload = function () {
        var moveToTop = document.getElementById('memento-login-button');
        let parrent = moveToTop.parentNode;
        parrent.insertBefore(moveToTop, parrent.childNodes[0]);
    }
</script>