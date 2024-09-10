document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const resultsNumDiv = document.getElementById('resutls_num');
    const resultsDiv = document.getElementById('results');
    let timeout;

    searchInput.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            performSearch(searchInput.value);
        }, 300); // 300ms debounce delay
    });

    function performSearch(query) {
        const endpoint = '/api/search'; // Replace with your actual endpoint
        const requestData = {
            table: 'docs',
            column: 'paragraph',
            query: query,
            limit: 6,
            offset: 0,
            fuzzy: true,
            andOp: false
        };

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    displayResults(data.result, query);
                } else {
                    resultsDiv.innerHTML = '<p>No results found.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultsDiv.innerHTML = '<p>Error fetching results.</p>';
            });
    }

    function displayResults(resultsObj, query) {
        resultsNumDiv.innerText = resultsObj.meta.total_results
        resultsDiv.innerHTML = '';
        resultsObj.data.forEach(result => {
            const paragraph = result.paragraph || '';
            const highlightedParagraph = highlightText(paragraph, query);
            const resultDiv = document.createElement('div');

            const link = result["file_path\r"].replace(".md", "").replaceAll("\\\\", "/").replace("pages/", "");
            const linkWithHash = link + "#" + result.heading.replace(/^#* /, "").replaceAll(" ", "-").toLowerCase();
            const pageName = link.split("/").at(-1);
            resultDiv.innerHTML = `
                <h3><a href="/${link}">${pageName}</a></h3>
                <p><a href="/${linkWithHash}">${highlightedParagraph}</a></p>
                <hr>
            `;
            resultsDiv.appendChild(resultDiv);
        });
    }

    function highlightText(text, word) {
        const regex = new RegExp(`(${word})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }
});
