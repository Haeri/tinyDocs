import os
import csv
import re
from pathlib import Path

def find_md_files(directory):
    """Recursively find all markdown files in a directory."""
    return list(Path(directory).rglob("*.md"))

def extract_paragraphs_and_headings(file_path):
    """Extract paragraphs and their closest headings from a markdown file."""
    with open(file_path, 'r', encoding='utf-8') as file:
        content = file.read()
        
    # Use regex to split the content into paragraphs and headings
    paragraphs = re.split(r'\n\s*\n', content)
    headings = [para for para in paragraphs if re.match(r'#+\s+', para)]

    results = []
    current_heading = "No heading"
    
    for para in paragraphs:
        if re.match(r'#+\s+', para):
            current_heading = para.strip()
        else:
            if para.strip():  # Only consider non-empty paragraphs
                cleaned_para = para.replace(",", "").strip()
                cleaned_heading = current_heading.replace(",", "").strip()
                results.append((cleaned_para, cleaned_heading, file_path))
    
    return results

def crawl_directory_and_generate_csv(directory, output_csv):
    """Crawl a directory for markdown files and generate a CSV with paragraphs and headings."""
    md_files = find_md_files(directory)
    all_data = []

    for md_file in md_files:
        all_data.extend(extract_paragraphs_and_headings(md_file))

    with open(output_csv, 'w', newline='', encoding='utf-8') as csvfile:
        csvwriter = csv.writer(csvfile)
        csvwriter.writerow(['paragraph', 'heading', 'file_path'])
        csvwriter.writerows(all_data)

# Example usage
directory_to_crawl = 'pages'
output_csv_path = 'docs.csv'
crawl_directory_and_generate_csv(directory_to_crawl, output_csv_path)
