PROC=xsltproc --xinclude --nonet
STYLESHEET=docbook.xsl

all: html

html:
	$(PROC) --stringparam current.docid main $(STYLESHEET) manual.xml

clean:
	rm -f ../public_html/doc/*.html
	