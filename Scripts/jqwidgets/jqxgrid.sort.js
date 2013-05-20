/*
jQWidgets v2.3.1 (2012-July-23)
Copyright (c) 2011-2012 jQWidgets.
License: http://jqwidgets.com/license/
*/

(function(a){a.jqx.dataview.sort=function(){this.sortby=function(p,g,l){var m=Object.prototype.toString;if(g==null){this.sortdata=null;this.refresh();return}if(g==undefined){g=true}if(g=="a"||g=="asc"||g=="ascending"||g==true){g=true}else{g=false}var h=p;this.sortfield=p;this.sortfielddirection=g?"asc":"desc";if(this.sortcache==undefined){this.sortcache={}}this.sortdata=[];var r=[];var n=false;if(h=="constructor"){h=""}if(!this.virtualmode&&this.sortcache[h]!=null){var b=this.sortcache[h];r=b._sortdata;if(b.direction==g){r.reverse()}else{if(!b.direction&&g){r.reverse()}n=true}if(r.length<this.totalrecords){this.sortcache={};n=false;r=[]}}Object.prototype.toString=(typeof p=="function")?p:function(){return this[p]};var f=this.records;var o=this;var j="";if(this.source.datafields){a.each(this.source.datafields,function(){if(this.name==p){if(this.type){j=this.type}return false}})}if(r.length==0){if(f.length){var c=f.length;for(i=0;i<c;i++){var k=f[i];if(k!=null){var q=k;var e=q.toString();r.push({sortkey:e,value:q,index:i})}}}else{var d=false;for(obj in f){var k=f[obj];if(k==undefined){d=true;break}var q=k;r.push({sortkey:q.toString(),value:q,index:obj})}if(d){a.each(f,function(s,t){r.push({sortkey:t.toString(),value:t,index:s})})}}}if(!n){if(l==null){r.sort(this._compare)}else{r.sort(l)}}if(!g){r.reverse()}Object.prototype.toString=m;this.sortdata=r;this.sortcache[h]={_sortdata:r,direction:g};this.reload(this.records,this.rows,this.filters,this.updated,true)},this.clearsortdata=function(){this.sortcache={};this.sortdata=[]};this._compare=function(c,b){var c=c.sortkey;var b=b.sortkey;if(c===undefined){c=null}if(b===undefined){b=null}if(c===null&&b===null){return 0}if(c===null&&b!==null){return 1}if(c!==null&&b===null){return -1}if(a.jqx.dataFormat){if(a.jqx.dataFormat.isNumber(c)&&a.jqx.dataFormat.isNumber(b)){if(c<b){return -1}if(c>b){return 1}return 0}else{if(a.jqx.dataFormat.isDate(c)&&a.jqx.dataFormat.isDate(b)){if(c<b){return -1}if(c>b){return 1}return 0}else{if(!a.jqx.dataFormat.isNumber(c)&&!a.jqx.dataFormat.isNumber(b)){c=String(c).toLowerCase();b=String(b).toLowerCase()}}}}try{if(c<b){return -1}if(c>b){return 1}}catch(d){var e=d}return 0};this._equals=function(c,b){return(this._compare(c,b)===0)}};a.extend(a.jqx._jqxGrid.prototype,{_rendersortcolumn:function(){var b=this;var c=this.getsortcolumn();if(this.sortdirection){a.each(this.columns.records,function(e,f){var d=a.data(document.body,"groupsortelements"+this.datafield);if(c==null||this.datafield!=c){a(this.sortasc).hide();a(this.sortdesc).hide();if(d!=null){d.sortasc.hide();d.sortdesc.hide()}}else{if(b.sortdirection.ascending){a(this.sortasc).show();a(this.sortdesc).hide();if(d!=null){d.sortasc.show();d.sortdesc.hide()}}else{a(this.sortasc).hide();a(this.sortdesc).show();if(d!=null){d.sortasc.hide();d.sortdesc.show()}}}})}},getsortcolumn:function(){if(this.sortcolumn){return this.sortcolumn}return null},removesort:function(){this.sortby(null)},sortby:function(c,e,d){if(this._loading){alert(this.loadingerrormessage);return false}if(c==null){e=null;c=this.sortcolumn}if(c){var b=this;if(d==undefined&&b.source.sortcomparer!=null){d=b.source.sortcomparer}if(e=="a"||e=="asc"||e=="ascending"||e==true){ascending=true}else{ascending=false}var f=b.getcolumn(c);if(f==undefined||f==null){return}if(e!=null){b.sortdirection={ascending:ascending,descending:!ascending}}else{b.sortdirection={ascending:false,descending:false}}if(e!=null){b.sortcolumn=c}else{b.sortcolumn=null}if(b.source.sort||b.virtualmode){b.dataview.sortfield=c;if(e==null){b.dataview.sortfielddirection=""}else{b.dataview.sortfielddirection=ascending?"asc":"desc"}if(b.source.sort){b.source.sort(c,e)}}else{b.dataview.sortby(c,e,d)}if(b.groupable&&b.groups.length>0){b._render(true,false,false);if(b._updategroupheadersbounds){b._updategroupheadersbounds()}}else{if(b.pageable){b.dataview.updateview()}b._updaterowsproperties();b.rendergridcontent(true)}b._raiseEvent(6,{sortinformation:b.getsortinformation()})}},_togglesort:function(d){var b=this;if(d.sortable&&b.sortable){var c=b.getsortinformation();var e=null;if(c.sortcolumn!=null&&c.sortcolumn==d.datafield){e=c.sortdirection.ascending;if(b.sorttogglestates>1){if(e==true){e=false}else{e=null}}else{e=!e}}else{e=true}b.sortby(d.datafield,e,null)}}})})(jQuery);