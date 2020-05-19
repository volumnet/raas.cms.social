jQuery(document).ready(function($) {
    $('[data-role="multitable"] [data-role="checkbox-all"]').hide();
    $('[data-role="multitable"] tr:has([data-level="0"]) input:checkbox').click();

    var $thisObj = $('[data-role="multitable"] tbody:eq(0)');

    var fold = function($obj, slowly) {
        var $children = getChildren($obj);
        var $allChildren = getAllChildren($obj);
        $obj.find('[data-role="fold-subtree"]').addClass('jsTreePlus').removeClass('jsTreeMinus');
        $obj.removeAttr('data-unfolded');
        if (slowly) {
            $allChildren.slideUp();
        } else {
            $allChildren.hide();
        }
    };
    var unfold = function($obj, slowly) {
        var $children = getChildren($obj);
        var $allChildren = getAllChildren($obj);
        var $unfoldedChildren;
        if ($allChildren.length) {
            $unfoldedChildren = $allChildren.filter('[data-unfolded]');
        }
        $obj.find('[data-role="fold-subtree"]').removeClass('jsTreePlus').addClass('jsTreeMinus');
        $obj.attr('data-unfolded', 'true');
        if (slowly) {
            $children.slideDown(function () {
                $unfoldedChildren.each(function () {
                    unfold($(this), slowly);
                })
            });
        } else {
            $children.show();
            $unfoldedChildren.each(function () {
                unfold($(this), slowly);
            })
        }
    };
    var getParent = function ($obj) {
        var level = parseInt($('[data-level]', $obj).attr('data-level'));
        var $temp = $obj.prev();
        var l;
        while ($temp.length > 0) {
            l = parseInt($temp.find('[data-level]').attr('data-level'));
            if (!isNaN(l) && (l == level - 1)) {
                return $temp;
            }
            $temp = $temp.prev()
        }
        return null;
    };
    var getAllParents = function ($obj) {
        var $parents = $();
        var $temp = $obj;
        var $p;
        while ($p = getParent($temp)) {
            $parents = $parents.add($p);
            $temp = $p;
        }
            console.log($parents)
        return $parents;
    };
    var getSelfAndParents = function ($obj) {
        var $temp = $obj;
        var $ps = getAllParents($obj);
        if ($ps.length) {
            $temp = $temp.add($ps);
        }
        return $ps;
    }
    var getChildren = function ($obj) {
        var level = parseInt($('[data-level]', $obj).attr('data-level'));
        var $children = $obj.nextUntil('tr:has([data-level="' + level + '"])', 'tr:has([data-level="' + (level + 1) + '"])');
        if ($children.length) {
            return $children;
        }
        return null;
    }
    var getAllChildren = function ($obj) {
        var $ch;
        var $children;
        $ch = $children = getChildren($obj);
        if ($ch) {
            $ch.each(function () {
                var $ch2 = getAllChildren($(this));
                if ($ch2) {
                    $children = $children.add($ch2);
                }
            });
        }
        return $children;
    }
    var selfAndAllChildren = function ($obj) {
        var $temp = $obj;
        var $ps = getAllChildren($obj);
        if ($ps.length) {
            $temp = $temp.add($ps);
        }
        return $ps;
    };

    // $('tr', $thisObj).on('click', function () {
    //     $(this).find('td').css('background', 'green');
    //     getAllParents($(this)) && getAllParents($(this)).find('td').css('background', 'yellow');
    //     getParent($(this)) && getParent($(this)).find('td').css('background', 'red');
    //     getAllChildren($(this)) && getAllChildren($(this)).find('td').css('background', 'cyan');
    //     getChildren($(this)) && getChildren($(this)).find('td').css('background', 'blue');
    // })


    $('tr', $thisObj).hide();
    $('tr:has([data-level="0"])', $thisObj).show();

    $('tr:has(input:checked), tr:not(:has(a.muted))', $thisObj).each(function () {
        var $p = getAllParents($(this));
        if ($p && $p.length) {
            $p.each(function () {
                unfold($(this));
            })
        }
    })
    $('tr', $thisObj).each(function () {
        var level = parseInt($('[data-level]', this).attr('data-level'));
        var $children = getChildren($(this));
        if ($children && $children.length) {
            $('td:eq(0)', this).append('<a href="#" class="jsTreePlus" data-role="fold-subtree"></a>');
        }
    });

    $thisObj.on('click', '.jsTreePlus[data-role="fold-subtree"]', function () { 
        unfold($(this).closest('tr'), true);
        return false;
    });
    $thisObj.on('click', '.jsTreeMinus[data-role="fold-subtree"]', function () { 
        fold($(this).closest('tr'), true);
        return false;
    });
});