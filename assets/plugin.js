$(function () {
    $(".plugin").each(function () {
        var $this = $(this);
        $this.attr("data-plugin").trim().split(' ').forEach(function (plug) {
            $this[plug.trim()]();
        });
    });
});