<script>
    document.body.onload = function () {
        let moveToTop = document.getElementById('linkly-sso-button');
        let parent = moveToTop.parentNode;
        parent.insertBefore(moveToTop, parent.childNodes[0]);
    }
</script>