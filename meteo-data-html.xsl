<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" />

    <xsl:template match="/">
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f0f8ff;
                        margin: 0;
                        padding: 0;
                    }
                    .meteo-card {
                        max-width: 700px;
                        margin: 30px auto;
                        padding: 25px;
                        background-color: #ffffff;
                        border-radius: 15px;
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                        text-align: center;
                    }
                    h2 {
                        font-size: 24px;
                        color: #333;
                    }
                    .periode {
                        margin: 20px 0;
                        padding: 15px;
                        background-color: #f9f9f9;
                        border-radius: 10px;
                        border: 1px solid #ddd;
                        text-align: left;
                    }
                    h3 {
                        font-size: 20px;
                        color: #555;
                        margin-bottom: 10px;
                    }
                    p {
                        font-size: 16px;
                        margin: 5px 0;
                        color: #666;
                    }
                    .symbole {
                        font-size: 24px;
                        vertical-align: middle;
                        margin-right: 10px;
                    }
                </style>
            </head>
            <body>
                <div class="meteo-card">
                    <h2>Prévisions météo du jour</h2>

                    <!-- Matin (8h) -->
                    <xsl:choose>
                        <xsl:when test="//echeance[@hour='8']">
                            <xsl:apply-templates select="//echeance[@hour='8']" />
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="periode">
                                <h3>Matin (8-12h)</h3>
                                <p>Données non disponibles pour cette période.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <!-- Midi (12h) -->
                    <xsl:choose>
                        <xsl:when test="//echeance[@hour='12']">
                            <xsl:apply-templates select="//echeance[@hour='12']" />
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="periode">
                                <h3>Après-midi (12-20h)</h3>
                                <p>Données non disponibles pour cette période.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <!-- Soir (20h) -->
                    <xsl:choose>
                        <xsl:when test="//echeance[@hour='20']">
                            <xsl:apply-templates select="//echeance[@hour='20']" />
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="periode">
                                <h3>Soir (20-8h)</h3>
                                <p>Données non disponibles pour cette période.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="echeance">
        <div class="periode">
            <h3>
                <xsl:choose>
                    <xsl:when test="@hour='8'">Matin (8-12h)</xsl:when>
                    <xsl:when test="@hour='12'">Après-midi (12-20h)</xsl:when>
                    <xsl:when test="@hour='20'">Soir (20-8h)</xsl:when>
                </xsl:choose>
            </h3>

            <!-- Température -->
            <p>
                <span class="symbole">
                    <xsl:choose>
                        <xsl:when test="number(temperature/level[1]) &lt; 278.15">❄️</xsl:when>
                        <xsl:when test="number(temperature/level[1]) &lt; 288.15">🌡️</xsl:when>
                        <xsl:otherwise>☀️</xsl:otherwise>
                    </xsl:choose>
                </span>
                <xsl:value-of
                    select="format-number(number(temperature/level[1]) - 273.15, '0.0')" />°C
            </p>

            <!-- Pression -->
            <p>
                <span class="symbole">🌡️</span> Pression: <xsl:value-of
                    select="format-number(number(pression/level), '0')" /> hPa
            </p>

            <!-- Humidité -->
            <p>
                <span class="symbole">💧</span> Humidité: <xsl:value-of
                    select="format-number(number(humidite/level), '0')" />%
            </p>

            <!-- Pluie -->
            <xsl:if test="number(pluie) > 0">
                <p>
                    <span class="symbole">🌧️</span> Risque de pluie
                </p>
            </xsl:if>

            <!-- Neige -->
            <xsl:if test="number(risque_neige) > 50">
                <p>
                    <span class="symbole">🌨️</span> Risque de neige
                </p>
            </xsl:if>

            <!-- Vent -->
            <p>
                <span class="symbole">💨</span> Vent: <xsl:value-of
                    select="format-number(number(vent_moyen/level), '0.0')" /> km/h <xsl:if
                    test="number(vent_rafales/level) > 0">(Rafales: <xsl:value-of
                        select="format-number(number(vent_rafales/level), '0.0')" /> km/h)</xsl:if>
                Direction: <xsl:value-of select="vent_direction/level" />°
            </p>

            <!-- Iso Zéro -->
            <p>
                <span class="symbole">❄️</span> Iso Zéro: <xsl:value-of select="iso_zero" /> m
            </p>

            <!-- Nébulosité -->
            <p>
                <span class="symbole">☁️</span> Nébulosité: <xsl:value-of
                    select="format-number(number(nebulosite/level[1]), '0')" />%
            </p>
        </div>
    </xsl:template>

</xsl:stylesheet>
