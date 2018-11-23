import { isArrayHasData } from '../core/util';
import { stringLength } from '../core/util/strings';
import { on, createEl, addClass, stopPropagation, measureDom, isHTML } from '../core/util/dom';
import Control from './Control';

const options = {
    'height': 28,
    'vertical': false,
    'position': 'top-right',
    'reverseMenu' : false,
    'items': {
        //default buttons
    }
};



class Toolbar extends Control {
   
    buildOn(map) {
        this._map = map;
        const dom = createEl('div');
        const ul = createEl('ul', 'maptalks-toolbar-hx');
        dom.appendChild(ul);

        if (this.options['vertical']) {
            addClass(dom, 'maptalks-toolbar-vertical');
        } else {
            addClass(dom, 'maptalks-toolbar-horizonal');
        }
        const me = this;

        function onButtonClick(fn, index, childIndex, targetDom) {
            const item = me._getItems()[index];
            return function (e) {
                stopPropagation(e);
                return fn({
                    'target': item,
                    'index': index,
                    'childIndex': childIndex,
                    'dom': targetDom
                });
            };
        }

        const items = this.options['items'];
        if (isArrayHasData(items)) {
            for (let i = 0, len = items.length; i < len; i++) {
                const item = items[i];
                const li = createEl('li');
                if (this.options['height'] !== 28) {
                    li.style.lineHeight = this.options['height'] + 'px';
                }
                li.style.height = this.options['height'] + 'px';
                li.style.cursor = 'pointer';
                if (isHTML(item['item'])) {
                    li.style.textAlign = 'center';
                    const itemSize = measureDom('div', item['item']);
                    //vertical-middle
                    li.innerHTML = '<div style="margin-top:' + (this.options['height'] - itemSize['height']) / 2 + 'px;">' + item['item'] + '</div>';
                } else {
                    li.innerHTML = item['item'];
                }
                if (item['click']) {
                    on(li, 'click', (onButtonClick)(item['click'], i, null, li));
                }
                if (isArrayHasData(item['children'])) {
                    const dropMenu = this._createDropMenu(i);
                    li.appendChild(dropMenu);
                    li._menu = dropMenu;
                    on(li, 'mouseover', function () {
                        this._menu.style.display = '';
                    });
                    on(li, 'mouseout', function () {
                        this._menu.style.display = 'none';
                    });
                }
                ul.appendChild(li);
            }
        }
        return dom;
    }

    _createDropMenu(index) {
        const me = this;

        function onButtonClick(fn, index, childIndex) {
            const item = me._getItems()[index]['children'][childIndex];
            return function (e) {
                stopPropagation(e);
                return fn({
                    'target': item,
                    'index': index,
                    'childIndex': childIndex
                });
            };
        }
        const menuDom = createEl('div', 'maptalks-dropMenu'),
            items = this._getItems(),
            len = items.length,
            menuUL = createEl('ul'),
            children = items[index]['children'];
        if (index === len - 1 && children) {
            menuDom.style.cssText = 'right: 0px;';
            menuUL.style.cssText = 'right: 0px;position: absolute;';
            if (this.options['reverseMenu']) {
                menuUL.style.bottom = 0;
            }
        }
        menuDom.appendChild(createEl('em', 'maptalks-ico'));

        let liWidth = 0;
        for (let i = 0, l = children.length; i < l; i++) {
            const size = stringLength(children[i]['item'], '12px');
            if (size.width > liWidth) {
                liWidth = size.width;
            }
        }
        for (let i = 0, l = children.length; i < l; i++) {
            const child = children[i];
            const li = createEl('li');
            li.innerHTML = '<a href="javascript:;">' + child['item'] + '</a>';
            li.style.cursor = 'pointer';
            li.style.width = (liWidth + 24) + 'px'; // 20 for text-intent
            on(li.childNodes[0], 'click', (onButtonClick)(child['click'], index, i));
            menuUL.appendChild(li);
        }
        if (this.options['vertical']) {
            const width = liWidth < 95 ? 95 : liWidth;
            if (this.options['reverseMenu']) {
                menuDom.style.right = -(width + 10 * 2) + 'px';
            } else {
                menuDom.style.left = -(width + 10 * 2) + 'px'; // 10: padding in .maptalks-toolbar-vertical li
            }
        } else if (this.options['reverseMenu']) {
            menuDom.style.bottom = '28px';
        } else {
            menuDom.style.top = '28px';
        }
        menuDom.appendChild(menuUL);
        menuDom.style.display = 'none';
        return menuDom;
    }

    _getItems() {
        return this.options['items'] || [];
    }
}

Toolbar.mergeOptions(options);

export default Toolbar;
