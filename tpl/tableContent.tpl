{foreach $values as $dado}
    <tr id="row-{$entity}-{$dado['id']}">
        {foreach item=name key=i from=$names}
            <td class="padding-16">
                {if $i === 0}
                    <label class="left">
                        <input type="checkbox" class="table-select" rel="{$dado['id']}" data-entity="{$entity}" style="margin: 15px 2rem 11px 0px;"/>
                    </label>
                {/if}
                {$dado[$name]}
            </td>
        {/foreach}

        <td class="tableActions">
            <button id="del-{$entity}-{$dado['id']}" onclick="deleteEntityData('{$entity}', {$dado['id']})"
                    class="right color-hover-red-pale btn-floating color-white opacity hover-opacity-off">
                <i class="material-icons">delete</i>
            </button>
            <button title="duplicar" id="dup-{$entity}-{$dado['id']}" onclick="duplicateEntityData('{$entity}', {$dado['id']})"
                    class="right color-hover-yellow-pale btn-floating color-white opacity hover-opacity-off">
                <i class="material-icons">content_copy</i>
            </button>
            <button id="edit-{$entity}-{$dado['id']}" onclick="editEntityData('{$entity}', {$dado['id']})"
                    class="right color-hover-blue-pale btn-floating color-white opacity hover-opacity-off">
                <i class="material-icons">edit</i>
            </button>
        </td>
    </tr>
{/foreach}