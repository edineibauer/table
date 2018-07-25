if (typeof tableNovo !== 'function') {
    var searchTime = !1;

    function tableNovo(entity) {
        var p = new RegExp(/s$/i);
        $("#btn-table-" + entity).panel(themeDashboard("<span class='left color-text-grey'>" + (p.test(entity) ? entity.substr(0, (entity.length - 1)) : entity).replace('_', ' ').replace('-', ' ') + "</span>", {
            lib: 'form-crud',
            file: 'api',
            entity: entity
        }, function () {
            loadingTable(entity);
            resetPagination(entity);
            readTable(entity)
        }))
    }

    function editEntityData(entity, id) {
        var $form = $("#table-" + entity);
        var cont = $form.find(".table-select:checked").length;
        if (cont > 1 && cont < 5) {
            $.each($form.find(".table-select"), function () {
                if ($(this).is(":checked"))
                    editEntityDataId(entity, parseInt($(this).attr("rel")))
            })
        } else {
            editEntityDataId(entity, id)
        }
    }

    function deleteEntityData(entity, id) {
        var $form = $("#table-" + entity);
        var cont = $form.find(".table-select:checked").length;
        if (confirm(cont > 1 ? "Excluir os " + cont + " Registros?" : "Excluir este Registro? ")) {
            loadingTable(entity);
            if (($form.find("tr").length - (cont === 0 ? 1 : cont)) === 1) {
                resetPagination(entity);
                readTable(entity)
            }
            if (cont > 1) {
                $.each($form.find(".table-select"), function () {
                    if ($(this).is(":checked"))
                        deleteEntityDataId(entity, parseInt($(this).attr("rel")))
                })
            } else {
                deleteEntityDataId(entity, id)
            }
        }
    }

    function duplicateEntityData(entity, id) {
        loadingTable(entity);
        resetPagination(entity);
        var $form = $("#table-" + entity);
        if ($form.find(".table-select:checked").length > 1) {
            $.each($form.find(".table-select"), function () {
                if ($(this).is(":checked"))
                    duplicateEntityDataId(entity, parseInt($(this).attr("rel")))
            })
        } else {
            duplicateEntityDataId(entity, id)
        }
    }

    function editEntityDataId(entity, id) {
        var p = new RegExp(/s$/i);
        $("#edit-" + entity + "-" + id).panel(themeDashboard("<span class='left color-text-grey'>" + (p.test(entity) ? entity.substr(0, (entity.length - 1)) : entity).replace('_', ' ').replace('-', ' ') + "</span>", {
            lib: 'form-crud',
            file: 'api',
            entity: entity,
            id: id
        }, function () {
            loadingTable(entity);
            readTable(entity)
        }))
    }

    function deleteEntityDataId(entity, id) {
        post('table', 'delete/data', {entity: entity, id: id}, function () {
            clearLoadTable(entity);
            $("#row-" + entity + "-" + id).remove()
        })
    }

    function duplicateEntityDataId(entity, id) {
        post('table', 'duplicate/data', {entity: entity, id: id}, function () {
            readTable(entity)
        })
    }

    function clearLoadTable(entity) {
        $("#table-" + entity + " tbody").removeClass("opacity").html('')
    }

    function readTable(entity) {
        let param = {
            entity: entity,
            limit: $("#limit-" + entity).val(),
            search: $("#search-" + entity).val(),
            offset: $("#table-pagina-" + entity).val(),
            filter: {title: $("#search-" + entity).val()}
        };
        var $table = $("#table-" + entity + " tbody");
        loadingTable(entity);
        post('table', 'read/data', param, function (data) {
            clearLoadTable(entity);
            $table.html(data.content);
            $("#table-cont-pag-" + entity).html(data.pagination + " \\");
            $("#table-total-" + entity).html(data.total);
            if ($('#pagination-' + entity).html() === "" && data.pagination > 1) {
                $('#pagination-' + entity).materializePagination({
                    lastPage: data.pagination,
                    onClickCallback: function (requestedPage) {
                        if (parseInt($("#table-pagina-" + entity).val()) !== requestedPage) {
                            $("#tableList-" + entity).loading();
                            $("#table-pagina-" + entity).val(requestedPage);
                            readTable(entity)
                        }
                    }
                })
            }
        })
    }

    function loadingTable(entity) {
        $("#table-" + entity + " tbody").addClass("opacity").append('<div class="loaderDashboard"><svg viewBox="0 0 32 32" width="32" height="32"><circle id="spinner" style="stroke: teal" cx="16" cy="16" r="14" fill="none"></circle></svg></div>')
    }

    function resetPagination(entity) {
        $('#pagination-' + entity).html("");
        $("#table-pagina-" + entity).val(1);
        $("#table-" + entity).find(".table-select-all").prop("checked", !1)
    }

    function startTable() {
        $.each($(".tableList"), function () {
            readTable($(this).attr("data-entity"))
        });
        $(".tableLimit").off("change").on("change", function () {
            var entity = $(this).attr("data-entity");
            loadingTable(entity);
            resetPagination(entity);
            readTable(entity)
        });
        $(".table-search").off("keyup change").on("keyup change", function () {
            var entity = $(this).attr("data-entity");
            loadingTable(entity);
            clearTimeout(searchTime);
            searchTime = setTimeout(function () {
                readTable(entity)
            }, 400)
        });
        $(".table-select-all").off("change").on("change", function () {
            $("#table-" + $(this).attr("data-entity")).find(".table-select").prop("checked", $(this).is(":checked"))
        });
        $(".table-all").off("change", ".table-select").on("change", ".table-select", function () {
            var all = !0;
            var $this = $(this);
            var $form = $("#table-" + $(this).attr("data-entity"));
            $.each($form.find(".table-select"), function () {
                if (all && $(this).is(":checked") !== $this.is(":checked"))
                    all = !1
            });
            $form.find(".table-select-all").prop("checked", (all && $this.is(":checked")))
        })
    }
}
$(function () {
    startTable()
})