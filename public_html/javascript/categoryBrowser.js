    // ***************
    // Used to connect the db/file browser with this document and the formfields on it!
    // ***************

    function setFormValueOpenBrowser (mode) {
        var url = "browser.php?mode="+mode;
        browserWin = window.open(url,"CategoryBrowser","height=450,width=400,status=0,menubar=0,resizable=1,scrollbars=1");
        browserWin.typoWin = self;
        browserWin.focus();
    }

    function setFormValueFromBrowseWin(fName,formBrowserObj)    {
        var formObj = setFormValue_getFObj(fName)
        
        if (formObj)    {
            fObj = formObj[fName+"[]"];
            var l = fObj.length;
            for(i=0;i<formBrowserObj.cat.length;++i) {
                if(formBrowserObj.cat.options[i].selected == true) {
                    fObj.length++;
                    fObj.options[l].value=formBrowserObj.cat.options[i].value;
                    fObj.options[l].text=formBrowserObj.cat.options[i].text;
                    l++;
                }
            }
            setHiddenFromList(fObj,formObj[fName+"_list"]);  
        } 
    }
    
    function addValue(value,text,fName)    {
        var formObj = setFormValue_getFObj(fName)
        if (formObj)    {
            fObj = formObj[fName+"[]"];
            var l = fObj.length;
            fObj.length++;
            fObj.options[l].value=value;
            fObj.options[l].text=text;
        }
    }

    function setHiddenFromList(fObjSel,fObjHid)    {
        l=fObjSel.length;
        fObjHid.value="";
        for (a=0;a<l;a++)    {
            fObjHid.value+=fObjSel.options[a].value+",";
        }
    }

    function setFormValueManipulate(fName,type)    {
        var formObj = setFormValue_getFObj(fName)
        if (formObj)    {
            var localArray_V = new Array();
            var localArray_L = new Array();
            var fObjSel = formObj[fName+"[]"];
            var l=fObjSel.length;
            var c=0;
            var cS=0;
            for (a=0;a<l;a++)    {
                if (fObjSel.options[a].selected!=1) {
                    localArray_V[c]=fObjSel.options[a].value;
                    localArray_L[c]=fObjSel.options[a].text;
                    c++;
                }
            }
            fObjSel.length = c;
            for (a=0;a<c;a++)    {
                fObjSel.options[a].value = localArray_V[a];
                fObjSel.options[a].text = localArray_L[a];
                fObjSel.options[a].selected=(a<cS)?1:0;
            }
            setHiddenFromList(fObjSel,formObj[fName+"_list"]);  
        }
    }

    function setFormValue_getFObj(fName)    {
        var formObj = document.edit;
        if (formObj)    {
            if (formObj[fName+"[]"] && formObj[fName+"_list"] && formObj[fName+"[]"].type=="select-multiple")    {
                return formObj;
            } else {    
                alert("Formfields missing:\n fName: "+formObj[fName]+"\n fName_list:"+formObj[fName+"_list"]+"\ntype:"+formObj[fName+"_list"].type+"\n fName:"+fName);
            }
        } else {
        	alert("documet.edit failed...");
        }
        return "";
    }

    // END: dbFileCon parts.

