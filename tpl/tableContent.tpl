{foreach $values as $dado}
    <tr id="row-{$entity}-{$dado['id']}">
        {foreach item=name key=i from=$names}
            {if $format[$i] === "source"}
                <td class="padding-16 tableImgTd" style="background-image: url(image/{$dado[$name]}&h=70&w=300);">
            {else}
                <td class="padding-16">
            {/if}
                {if $i === 0}
                    <label class="left">
                        <input type="checkbox" class="table-select" rel="{$dado['id']}" data-entity="{$entity}"
                               style="margin: 15px 2rem 11px 0px;"/>
                    </label>
                {/if}
                {if $name == $status}
                    {if !$buttons.status}
                        {if $dado[$name]}
                            <span class='color-green tag'>&nbsp;ON&nbsp;</span>
                        {else}
                            <span class='color-orange tag color-text-white'>OFF</span>
                        {/if}
                    {/if}
                {elseif $format[$i] === "source"}
                {else}
                    {$dado[$name]}
                {/if}
            </td>
        {/foreach}

        <td class="tableActions{if $dado.permission == false} disabled" title="Sem Permissão{/if}">
            {if $buttons.delete}
                <button id="del-{$entity}-{$dado['id']}" onclick="deleteEntityData('{$entity}', {$dado['id']})"
                        class="right btn-floating color-white color-hover-text-red hover-shadow opacity hover-opacity-off">
                    <i class="material-icons">delete</i>
                </button>
            {/if}
            {$buttons.copy}
            {if $buttons.copy}
                <button title="duplicar" id="dup-{$entity}-{$dado['id']}"
                        onclick="duplicateEntityData('{$entity}', {$dado['id']})"
                        class="right color-hover-text-green btn-floating color-white hover-shadow opacity hover-opacity-off">
                    <i class="material-icons">content_copy</i>
                </button>
            {/if}
            {if $buttons.edit}
                <button id="edit-{$entity}-{$dado['id']}" onclick="editEntityData('{$entity}', {$dado['id']})"
                        class="right btn-floating color-white hover-shadow opacity hover-opacity-off">
                    <i class="material-icons">edit</i>
                </button>
            {/if}
            {if $buttons.status && $status}
                <label class="right">
                    <div class="switch switch-squad margin-0 margin-right">
                        <input type="checkbox" class="switch-status-table" data-status="{$status}" data-entity="{$entity}" rel="{$dado.id}"
                                {($dado[$status]) ? "checked='checked' " : "" }
                               class="switchCheck"/>
                        <div class="slider"></div>
                    </div>
                </label>
            {/if}
        </td>
    </tr>
{/foreach}