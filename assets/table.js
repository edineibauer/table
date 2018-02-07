function novo(entity) {
    $("#btn-table-" + entity).panel(themeWindow("Novo " + entity, {lib:'form-crud', file: 'read/form', entity: entity}, function () {
        readTable(entity);
    }));
}

function readTable(entity) {
    post('table-list', '')
}