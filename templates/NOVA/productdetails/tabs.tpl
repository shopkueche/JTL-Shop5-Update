{block name='productdetails-tabs'}
    {block name='productdetails-tabs-settings'}
        {$tabanzeige = $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
        {$showProductWeight = false}
        {$showShippingWeight = false}
        {if isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0
        && $Einstellungen.artikeldetails.artikeldetails_artikelgewicht_anzeigen === 'Y'}
            {$showProductWeight = true}
        {/if}
        {if isset($Artikel->cGewicht) && $Artikel->fGewicht > 0
        && $Einstellungen.artikeldetails.artikeldetails_gewicht_anzeigen === 'Y'}
            {$showShippingWeight = true}
        {/if}
        {$dimension = $Artikel->getDimension()}
        {$funcAttr = $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]|default:0}
        {$showAttributesTable = ($Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'
        && !empty($Artikel->oMerkmale_arr) || $showProductWeight || $showShippingWeight
        || $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'
        && (!empty($dimension['length']) || !empty($dimension['width']) || !empty($dimension['height']))
        || isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0
        && $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
        || ($Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || $funcAttr == 1)
        && !empty($Artikel->Attribute))}
        {$useDescriptionWithMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA'
        && $Artikel->cMedienDateiAnzeige !== 'tab') || $Artikel->cMedienDateiAnzeige === 'beschreibung')
        && !empty($Artikel->getMediaTypes()))}
        {$useDescription = (($Artikel->cBeschreibung|strlen > 0) || $useDescriptionWithMediaGroup || $showAttributesTable)}
        {$useDownloads = (isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0)}
        {$useVotes = $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
        {$useQuestionOnItem = $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
        {$usePriceFlow = ($Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf)}
        {$useAvailabilityNotification = ($verfuegbarkeitsBenachrichtigung !== 0)}
        {$useMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM'
        && $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab')
        && !empty($Artikel->getMediaTypes()))}
        {$hasVotesHash = isset($smarty.get.ratings_nPage)
        || isset($smarty.get.bewertung_anzeigen)
        || isset($smarty.get.ratings_nItemsPerPage)
        || isset($smarty.get.ratings_nSortByDir)
        || isset($smarty.get.btgsterne)}
        {section name=iterator start=1 loop=10}
            {$tab = tab}
            {$tabname = $tab|cat:$smarty.section.iterator.index|cat:" name"}
            {$tabinhalt = $tab|cat:$smarty.section.iterator.index|cat:" inhalt"}
            {if isset($Artikel->AttributeAssoc[$tabname]) && $Artikel->AttributeAssoc[$tabname]
            && $Artikel->AttributeAssoc[$tabinhalt]}
                {$separatedTabs[{$tabname|replace:' ':'-'}] = [
                'id'      => {$tabname|replace:' ':'-'},
                'name'   => {$Artikel->AttributeAssoc[$tabname]},
                'content' => {$Artikel->AttributeAssoc[$tabinhalt]}
                ]}
            {/if}
        {/section}
        {$setActiveClass = [
        'description'    => (!$hasVotesHash),
        'downloads'      => (!$hasVotesHash && !$useDescription),
        'separatedTabs'  => (!$hasVotesHash && !$useDescription && !$useDownloads),
        'votes'          => ($hasVotesHash || !$useDescription && !$useDownloads && empty($separatedTabs)),
        'questionOnItem' => (!$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs) && !$useVotes),
        'priceFlow'      => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
        && !$useQuestionOnItem),
        'availabilityNotification' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads
        && empty($separatedTabs) && !$useQuestionOnItem && !$usePriceFlow),
        'mediaGroup' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
        && !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification)
        ]}
    {/block}
    {block name='productdetails-tabs-content'}
        {if useDescription || $useDownloads || $useDescriptionWithMediaGroup || $useVotes || $useQuestionOnItem || $usePriceFlow
        || $useAvailabilityNotification || $useMediaGroup || !empty($separatedTabs)}
            {if $tabanzeige && !$isMobile}
                {block name='productdetails-tabs-tabs'}
                    {opcMountPoint id='opc_before_tabs' inContainer=false}
                    {container}
                        <nav class="tab-navigation">
                        {tabs id="product-tabs"}
                        {if $useDescription}
                            {block name='productdetails-tabs-tab-description'}
                                {tab title="{lang key="description" section="productDetails"}" active=$setActiveClass.description id="description"}
                                    {block name='productdetails-tabs-tab-content'}
                                        {block name='tab-description-media-types'}
                                            {opcMountPoint id='opc_before_desc'}
                                            <div class="desc">
                                                <p>{$Artikel->cBeschreibung}</p>
                                                {if $useDescriptionWithMediaGroup}
                                                    {foreach $Artikel->getMediaTypes() as $mediaType}
                                                        <div class="h3">{$mediaType->name}</div>
                                                        <div class="media">
                                                            {include file='productdetails/mediafile.tpl'}
                                                        </div>
                                                    {/foreach}
                                                {/if}
                                            </div>
                                            {opcMountPoint id='opc_after_desc'}
                                        {/block}
                                        {block name='productdetails-tabs-tab-description-include-attributes'}
                                            {include file='productdetails/attributes.tpl' tplscope='details'
                                            showProductWeight=$showProductWeight showShippingWeight=$showShippingWeight
                                            dimension=$dimension showAttributesTable=$showAttributesTable}
                                        {/block}
                                    {/block}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useDownloads}
                            {block name='productdetails-tabs-tab-downloads'}
                                {tab title="{lang section="productDownloads" key="downloadSection"}" active=$setActiveClass.downloads id="downloads"}
                                    {include file='productdetails/download.tpl'}
                                {/tab}
                            {/block}
                        {/if}

                        {if !empty($separatedTabs)}
                            {block name='productdetails-tabs-tab-separated-tabs'}
                                {foreach $separatedTabs as $separatedTab}
                                    {tab title=$separatedTab.name active=$setActiveClass.separatedTabs && $separatedTab@first id="{$separatedTab.name|@seofy}"}
                                        {$separatedTab.content}
                                    {/tab}
                                {/foreach}
                            {/block}
                        {/if}

                        {if $useVotes}
                            {block name='productdetails-tabs-tab-votes'}
                                {tab title="{lang key='Votes'}" active=$setActiveClass.votes id="votes"}
                                    {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useQuestionOnItem}
                            {block name='productdetails-tabs-tab-question-on-item'}
                                {tab title="{lang key="productQuestion" section="productDetails"}" active=$setActiveClass.questionOnItem id="questionOnItem"}
                                    {include file='productdetails/question_on_item.tpl' position="tab"}
                                {/tab}
                            {/block}
                        {/if}

                        {if $usePriceFlow}
                            {block name='productdetails-tabs-tab-price-flow'}
                                {tab title="{lang key='priceFlow' section='productDetails'}" active=$setActiveClass.priceFlow id="priceFlow"}
                                    {include file='productdetails/price_history.tpl'}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useAvailabilityNotification}
                            {block name='productdetails-tabs-tab-availability-notification'}
                                {tab title="{lang key='notifyMeWhenProductAvailableAgain'}" active=$setActiveClass.availabilityNotification id="tab-availabilityNotification"}
                                    {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useMediaGroup}
                            {block name='productdetails-tabs-tab-mediagroup'}
                                {foreach $Artikel->getMediaTypes() as $mediaType}
                                    {$cMedienTypId = $mediaType->name|@seofy}
                                    {tab title="{$mediaType->name} ({$mediaType->count})" active=$setActiveClass.mediaGroup && $mediaType@first id="{$cMedienTypId}"}
                                        {include file='productdetails/mediafile.tpl'}
                                    {/tab}
                                {/foreach}
                            {/block}
                        {/if}
                        {/tabs}
                        </nav>
                    {/container}
                {/block}
            {else}
                {block name='productdetails-tabs-no-tabs'}
                    {container}
                        <div class="accordion" id="tabAccordion">
                            {if $useDescription}
                                {block name='productdetails-tabs-description'}
                                    {card no-body=true}
                                        {cardheader id="tab-description-head"
                                            data=["toggle" => "collapse",
                                                "target"=>"#tab-description"
                                            ]
                                            aria=["expanded" => "{if $setActiveClass.description}true{else}false{/if}",
                                                "controls" => "tab-description"
                                            ]
                                        }
                                            {lang key='description' section='productDetails'}
                                        {/cardheader}
                                        {collapse id="tab-description" visible=$setActiveClass.description
                                            data=["parent"=>"#tabAccordion"]
                                            aria=["labelledby"=>"tab-description-head"]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-card-description'}
                                                    {block name='productdetails-tabs-card-description-content'}
                                                        {opcMountPoint id='opc_before_desc'}
                                                        <div class="desc">
                                                            {$Artikel->cBeschreibung}
                                                            {if $useDescriptionWithMediaGroup}
                                                                {if $Artikel->cBeschreibung|strlen > 0}
                                                                    <hr>
                                                                {/if}
                                                                {foreach $Artikel->getMediaTypes() as $mediaType}
                                                                    <div class="media">
                                                                        {block name='productdetails-tabs-description-include-mediafile'}
                                                                            {include file='productdetails/mediafile.tpl'}
                                                                        {/block}
                                                                    </div>
                                                                {/foreach}
                                                            {/if}
                                                        </div>
                                                        {opcMountPoint id='opc_after_desc'}
                                                    {/block}
                                                    {block name='productdetails-tabs-card-description-attributes'}
                                                        {block name='productdetails-tabs-include-attributes'}
                                                            {include file='productdetails/attributes.tpl' tplscope='details'
                                                            showProductWeight=$showProductWeight showShippingWeight=$showShippingWeight
                                                            dimension=$dimension showAttributesTable=$showAttributesTable}
                                                        {/block}
                                                    {/block}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useDownloads}
                                {block name='productdetails-tabs-downloads'}
                                    {card no-body=true}
                                        {cardheader id="tab-downloads-head"
                                            data=["toggle" => "collapse",
                                                "target"=>"#tab-downloads"
                                            ]
                                            aria=["expanded" => "{if $setActiveClass.downloads}true{else}false{/if}",
                                                "controls" => "tab-downloads"
                                            ]
                                        }
                                            {lang section='productDownloads' key='downloadSection'}
                                        {/cardheader}
                                        {collapse id="tab-downloads" visible=$setActiveClass.downloads
                                            data=["parent"=>"#tabAccordion"]
                                            aria=["labelledby"=>"tab-downloads-head"]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-download'}
                                                    {include file='productdetails/download.tpl'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if !empty($separatedTabs)}
                                {block name='productdetails-tabs-separated-tabs'}
                                    {foreach $separatedTabs as $separatedTab}
                                        {$separatedTabId = $separatedTab.name|@seofy}
                                        {card no-body=true}
                                            {cardheader id="tab-{$separatedTabId}-head"
                                                data=["toggle" => "collapse",
                                                    "target"=>"#tab-{$separatedTabId}"
                                                ]
                                                aria=["expanded" => "{if $setActiveClass.separatedTabs && $separatedTab@first}true{else}false{/if}",
                                                    "controls" => "tab-{$separatedTabId}"
                                                ]
                                            }
                                                {$separatedTab.name}
                                            {/cardheader}
                                            {collapse id="tab-{$separatedTabId}" visible=($setActiveClass.separatedTabs && $separatedTab@first)
                                                data=["parent"=>"#tabAccordion"]
                                                aria=["labelledby"=>"tab-{$separatedTabId}-head"]
                                            }
                                                {cardbody}
                                                    {$separatedTab.content}
                                                {/cardbody}
                                            {/collapse}
                                        {/card}
                                    {/foreach}
                                {/block}
                            {/if}

                            {if $useVotes}
                                {block name='productdetails-tabs-votes'}
                                    {card no-body=true }
                                        {cardheader id="tab-votes-head"
                                            data=["toggle" => "collapse",
                                                "target"=>"#tab-votes"
                                            ]
                                            aria=["expanded" => "{if $setActiveClass.votes}true{else}false{/if}",
                                                "controls" => "tab-votes"
                                            ]
                                        }
                                            {lang key='Votes'}
                                        {/cardheader}
                                        {collapse id="tab-votes" visible=$setActiveClass.votes
                                            data=["parent"=>"#tabAccordion"]
                                            aria=["labelledby"=>"tab-votes-head"]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-reviews'}
                                                    {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useQuestionOnItem}
                                {block name='productdetails-tabs-question-on-item'}
                                    {card no-body=true}
                                        {cardheader id="tab-question-head"
                                            data=["toggle" => "collapse",
                                                "target"=>"#tab-question"
                                            ]
                                            aria=["expanded" => "{if $setActiveClass.questionOnItem}true{else}false{/if}",
                                                "controls" => "tab-question"
                                            ]
                                        }
                                            {lang key='productQuestion' section='productDetails'}
                                        {/cardheader}
                                        {collapse id="tab-question" visible=$setActiveClass.questionOnItem
                                            data=["parent"=>"#tabAccordion"]
                                            aria=["labelledby"=>"tab-question-head"]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-question-on-item'}
                                                    {include file='productdetails/question_on_item.tpl' position="tab"}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $usePriceFlow}
                                {block name='productdetails-tabs-price-flow'}
                                    {card no-body=true}
                                        {cardheader id="tab-priceFlow-head"
                                            data=["toggle" => "collapse",
                                                "target"=>"#tab-priceFlow"
                                            ]
                                            aria=["expanded" => "{if $setActiveClass.priceFlow}true{else}false{/if}",
                                                "controls" => "tab-priceFlow"
                                            ]
                                        }
                                            {lang key='priceFlow' section='productDetails'}
                                        {/cardheader}
                                        {collapse id="tab-priceFlow" visible=$setActiveClass.priceFlow
                                            data=["parent"=>"#tabAccordion"]
                                            aria=["labelledby"=>"tab-priceFlow-head"]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-price-history'}
                                                    {include file='productdetails/price_history.tpl'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useAvailabilityNotification}
                                {block name='productdetails-tabs-availability-notification'}
                                    {card no-body=true}
                                        {cardheader id="tab-availabilityNotification-head"
                                            data=["toggle" => "collapse",
                                                "target"=>"#tab-availabilityNotification"
                                            ]
                                            aria=["expanded" => "{if $setActiveClass.availabilityNotification}true{else}false{/if}",
                                                "controls" => "tab-availabilityNotification"
                                            ]
                                        }
                                        {lang key='notifyMeWhenProductAvailableAgain'}
                                        {/cardheader}
                                        {collapse id="tab-availabilityNotification" visible=$setActiveClass.availabilityNotification
                                            data=["parent"=>"#tabAccordion"]
                                            aria=["labelledby"=>"tab-availabilityNotification-head"]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-availability-notification-form'}
                                                    {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useMediaGroup}
                                {block name='productdetails-tabs-media-gorup'}
                                    {foreach $Artikel->getMediaTypes() as $mediaType}
                                        {$cMedienTypId = $mediaType->name|@seofy}
                                        {card no-body=true}
                                            {cardheader id="tab-{$cMedienTypId}-head"
                                                data=["toggle" => "collapse",
                                                    "target"=>"#tab-{$cMedienTypId}"
                                                ]
                                                aria=["expanded" => "{if $setActiveClass.mediaGroup && $mediaType@first}true{else}false{/if}",
                                                    "controls" => "tab-{$cMedienTypId}"
                                                ]
                                            }
                                                {$mediaType->name}
                                            {/cardheader}
                                            {collapse id="tab-{$cMedienTypId}" visible=($setActiveClass.mediaGroup && $mediaType@first)
                                                data=["parent"=>"#tabAccordion"]
                                                aria=["labelledby"=>"tab-{$cMedienTypId}-head"]
                                            }
                                                {cardbody}
                                                    {block name='productdetails-tabs-include-mediafile'}
                                                        {include file='productdetails/mediafile.tpl'}
                                                    {/block}
                                                {/cardbody}
                                            {/collapse}
                                        {/card}
                                    {/foreach}
                                {/block}
                            {/if}
                        </div>
                    {/container}
                {/block}
            {/if}
        {/if}
    {/block}
{/block}
