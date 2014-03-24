/* Clearly, all this is stolen from delicious */

function set(val) {
    $id('formtitle').value = val;
}


String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, '')
}
String.prototype.unescHtml = function () {
    var i, e = {'&lt;': '<', '&gt;': '>', '&amp;': '&', '&quot;': '"'}, t = this;
    for (i in e) t = t.replace(new RegExp(i, 'g'), e[i]);
    return t
}

// styling functions
function isA(o, klass) {
    if (!o.className) return false;
    return new RegExp('\\b' + klass + '\\b').test(o.className)
}
function addClass(o, klass) {
    if (!isA(o, klass)) o.className += ' ' + klass
}
function rmClass(o, klass) {
    o.className = o.className.replace(new RegExp('\\s*\\b' + klass + '\\b'), '')
}
function swapClass(o, klass, klass2) {
    var swap = isA(o, klass) ? [klass, klass2] : [klass2, klass];
    rmClass(o, swap[0]);
    addClass(o, swap[1])
}
function getStyle(o, s) {
    if (document.defaultView && document.defaultView.getComputedStyle) return document.defaultView.getComputedStyle(o, null).getPropertyValue(s)
    else if (o.currentStyle) {
        return o.currentStyle[s.replace(/-([^-])/g, function (a, b) {
            return b.toUpperCase()
        })]
    }
}
// shorter names for grabbing stuff
function $id(id) {
    return document.getElementById(id)
}
function $tags(t, o) {
    o = o || document;
    return o.getElementsByTagName(t)
}
function $tag(t, o, i) {
    o = o || document;
    return o.getElementsByTagName(t)[i || 0]
}
// get elements by class name, eg $c('post', document, 'li')
function $c(c, o, t) {
    o = o || document;
    if (!o.length) o = [o]
    else if (o.length == 1 && !o[0]) o = [o] // opera, you're weird
    var elements = []
    for (var i = 0, e; e = o[i]; i++) {
        if (e.getElementsByTagName) {
            var children = e.getElementsByTagName(t || '*')
            for (var j = 0, child; child = children[j]; j++) if (isA(child, c)) elements.push(child)
        }
    }
    return elements
}

Function.prototype.bind = function (o) {
    var __method = this
    return function () {
        return __method.apply(o, arguments)
    }
}

String.prototype.escRegExp = function () {
    return this.replace(/[\\$*+?()=!|,{}\[\]\.^]/g, '\\$&')
}
String.prototype.unescHtml = function () {
    var i, t = this;
    for (i in e) t = t.replace(new RegExp(i, 'g'), e[i]);
    return t
}
function Suggestions() {
    this.length = 0;
    this.picked = 0
}
var suggestions = new Suggestions()
var tagSearch = '', lastEdit = ''
var h = {}, sections = [
    {},
    {},
    {},
    {},
    {},
    {}
], selected = {}, currentTag = {}, e = {'&lt;': '<', '&gt;': '>', '&amp;': '&', '&quot;': '"'}

function init() {
    var elements = ['alpha', 'copy', 'freq', 'network'], divs = {}, freqSort = [], freqMap = {}, t, i

    for (i in elements) divs[elements[i]] = makeDiv(elements[i] + 'tags')
    elements = elements.concat('suggest', 'tags', 'alphasort', 'freqsort')
    for (i in elements) h[elements[i]] = $id(elements[i])
    for (t in tags) {
        if (!freqMap[tags[t]]) {
            freqMap[tags[t]] = {};
            freqSort[freqSort.length] = tags[t]
        }
        freqMap[tags[t]][t] = true;
        sections[0][t.toLowerCase()] = makeTag(divs.alpha, t, 'swap')
    }
    freqSort.sort(function (a, b) {
        return b - a
    })
    for (i in freqSort) {
        for (t in freqMap[freqSort[i]]) {
            tagSearch += t + ' '
        }
    }
    for (t in copytags) {
        t = copytags[t]
        sections[4][t.toLowerCase()] = makeTag(divs.copy, t, 'swap')
        if (!sections[0][t]) tagSearch += t + ' '
    }
    if (copytags.length > 0) {
        h.copy.style.display = 'block'
        h.copy.appendChild(divs.copy)
    }
    if (freqSort.length > 0) {
        h.alpha.appendChild(divs.alpha);
    }
    /*        document.onkeydown = document.onkeypress = document.onkeyup = handler
     updateHilight()
     */
    /*        if ($id('formtitle')) { focusTo($id('formtitle'),0) } */
}

function makeDiv(id) {
    var obj = document.createElement('div');
    obj.id = id;
    return obj
}

function makeTag(parent, tag, js) {
    parent.appendChild(document.createTextNode(' '))
    var obj = document.createElement('a')
    obj.className = 'tag'
    obj.setAttribute('href', 'javascript:' + js + '("' + tag.replace(/"/g, '\\"') + '")')
    obj.appendChild(document.createTextNode(tag))
    if (tags[tag] < 4) obj.className += ' uncommon'
    if (tags[tag] < 2) obj.style.color = '#66f'
    if (tags[tag] == 2) obj.style.color = '#44f'
    parent.appendChild(obj)
    return obj
}

function select(t) {
    var i;
    t = t.toLowerCase()
    selected[t] = true;
    for (i in sections) if (sections[i][t]) addClass(sections[i][t], 'selected')
}
function deselect(t) {
    var i;
    t = t.toLowerCase()
    delete selected[t];
    for (i in sections) if (sections[i][t]) rmClass(sections[i][t], 'selected')
}

function swap(tag) {
    var tagArray = h.tags.value.trim().split(' '), present = false, t, tl = tag.toLowerCase()
    if (tagArray[0].trim() == '') tagArray.splice(0, 1);
    for (t = 0; t < tagArray.length; t++) {
        if (tagArray[t].toLowerCase() == tl) {
            tagArray.splice(t, 1);
            deselect(tag);
            present = true;
            t -= 1
        }
    }
    if (!present) {
        tagArray.push(tag);
        select(tag)
    }
    var content = tagArray.join(' ')
    lastEdit = h.tags.value = (content.length > 1) ? content + ' ' : content
    hideSuggestions()
    focusTo(h.tags)
}

function complete(tag) {
    var tagArray = h.tags.value.split(' ')
    if (typeof tag == 'undefined') tag = suggestions[suggestions.picked].innerHTML.unescHtml() // tab complete rather than click complete
    tagArray[currentTag.index] = tag
    var text = tagArray.join(' ')
    h.tags.value = (text.substr(-1, 1) == ' ' ? text : text + ' ' )
    hideSuggestions()
    updateHilight()
    focusTo(h.tags)
    $id("tags").blur();   //hack to "wake up" safari
    $id("tags").focus();
}

// focus the caret to end of a form input (+ optionally select some text)
var range = 0 //ie
function focusTo(obj, selectFrom) {
    if (typeof selectFrom == 'undefined') selectFrom = obj.value.length
    if (obj.createTextRange) { //ie + opera
        if (range == 0) range = obj.createTextRange()
        range.moveEnd("character", obj.value.length)
        range.moveStart("character", selectFrom)
        //obj.select()
        //range.select()
        setTimeout('range.select()', 10)
    } else if (obj.setSelectionRange) { //ff
        obj.select()
        obj.setSelectionRange(selectFrom, obj.value.length)
    } else { //safari :(
        obj.blur()
    }
}

function sort(text) {
    var lists = ['alpha', 'freq'], l
    for (l in lists) {
        l = lists[l]
//                  h[l].style.display = (l == text) ? 'inline' : 'none'
        h[l + 'sort'].className = (l == text) ? 'noclicky' : 'clicky'
    }
    var i, uncommon
    uncommon = $c('uncommon', document, 'a')
    for (i in uncommon) {
        i = uncommon[i]
        i.style.display = ('alpha' == text) ? 'inline' : 'none'
    }


}

function updateHilight() {
    var tagArray = h.tags.value.toLowerCase().split(' '), tagHash = {}
    if (tagArray[0].trim() == '') tagArray.splice(0, 1);
    for (t in tagArray) {
        if (tagArray[t] != '') {
            select(tagArray[t])
            tagHash[tagArray[t]] = true
        }
    }
    for (t in selected) {
        if (!tagHash[t]) deselect(t)
    }
    return [tagArray, tagHash]
}

function hideSuggestions() {
    h.suggest.parentNode.parentNode.style.visibility = 'hidden'
}
function showSuggestions() {
    suggest(0);
    h.suggest.parentNode.parentNode.style.visibility = 'visible'
}

function updateSuggestions() {
    if (!getCurrentTag() || !currentTag.text) {
        hideSuggestions();
        return false
    }

    while (h.suggest.hasChildNodes()) h.suggest.removeChild(h.suggest.firstChild)
    delete suggestions;
    suggestions = new Suggestions();
    var tagArray = h.tags.value.toLowerCase().split(' '), txt = currentTag.text.escRegExp(), tagHash = {}, t
    for (t in tagArray) tagHash[tagArray[t]] = true

    var search = tagSearch.match(new RegExp(("(?:^| )(" + txt + "[^ ]+)"), "gi"))
    if (search) {
        for (i = 0; i < search.length && suggestions.length < 10; i++) {
            tl = search[i].trim()
            if (tagHash[tl])  continue // do not suggest already typed tag
            suggestions[suggestions.length] = makeTag(h.suggest, tl, 'complete')
            suggestions.length++
        }
    }
    if (suggestions.length > 0) showSuggestions()
    else hideSuggestions()
}

function suggest(index) {
    if (suggestions.length == 1) index = 0
    if (suggestions[suggestions.picked]) suggestions[suggestions.picked].className = 'tag'
    suggestions[suggestions.picked = index].className = 'tag selected'
}

function getCurrentTag() {
    if (h.tags.value == lastEdit) return true // no edit
    if (h.tags == '') return false
    currentTag = {}
    var tagArray = h.tags.value.toLowerCase().split(' '), oldArray = lastEdit.toLowerCase().split(' '), currentTags = [], matched = false, t, o
    for (t in tagArray) {
        for (o in oldArray) {
            if (typeof oldArray[o] == 'undefined') {
                oldArray.splice(o, 1);
                break
            }
            if (tagArray[t] == oldArray[o]) {
                matched = true;
                oldArray.splice(o, 1);
                break;
            }
        }
        if (!matched) currentTags[currentTags.length] = t
        matched = false
    }
    // more than one word changed... abort
    if (currentTags.length > 1) {
        hideSuggestions();
        return false
    }
    currentTag = { text: tagArray[currentTags[0]], index: currentTags[0] }
    return true
}

function handler(event) {
    var e = (event || window.event) //w3||ie
    if (e.type == 'keydown') {
        if (suggestions.length > 0) {
            switch (e.keyCode) {
                case 38:
                    suggest((suggestions.picked + 1) % suggestions.length);
                    break
                case 40:
                    suggest(suggestions.picked == 0 ? suggestions.length - 1 : suggestions.picked - 1);
                    break

            }
        }
    } else if (e.type == 'keypress') {
        switch (e.keyCode) {
            case 38:
            case 40:
                if (e.preventDefault && e.originalTarget) e.preventDefault() //ff
                break;
            case 9: // tab
                if (e.preventDefault && h.suggest.parentNode.parentNode.style.visibility == 'visible') { //ff
                    complete()
                    e.preventDefault()
                }
                break;
            case 34432: //case 13: // enter
                if (h.suggest.parentNode.parentNode.style.visibility == 'hidden') {
                    submitForm();
                } else {

                    if (e.preventDefault && h.suggest.parentNode.parentNode.style.visibility == 'visible') { //ff
                        complete()
                        e.preventDefault()

                    }
                    ;
                }
                ;

                return false;
                break;
            default:
                lastEdit = h.tags.value
        }
    } else if (e.type == 'keyup') {
        updateHilight()
        switch (e.keyCode) {
            //case 8:  //backspace
            //case 46: //delete
            case 35: //end
            case 36: //home
            case 39: // right
            case 37: // left
            case 32: // space
                hideSuggestions();
                break
            case 38:
            case 40:
                break;
            case 9:
                if (!e.preventDefault && h.suggest.parentNode.parentNode.style.visibility == 'visible') complete() //ie
                break;
                // case 1432234432: case 13:
                if (h.suggest.parentNode.parentNode.style.visibility == 'hidden') {
                } else {
                    if (!e.preventDefault && h.suggest.parentNode.parentNode.style.visibility == 'visible') {
                        complete();
                    }
                    ;
                }

                return false;
                break;
            default:
                updateSuggestions()
        }
    }
}
