# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take security vulnerabilities seriously. Please do not report security vulnerabilities through public GitHub issues.

Instead, please report them via email to security@metastrip.org (replace with appropriate contact).

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the following information:
- Type of vulnerability
- Full path of source file(s) related to the vulnerability
- Location of the affected source code (tag/branch/commit or direct URL)
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue

## Security Best Practices When Using MetaStrip Image

1. **Input Validation**
   - Always validate image files before processing
   - Use appropriate file type checks
   - Implement file size limits

2. **Output Handling**
   - Validate processed images before saving
   - Use safe file paths for output
   - Implement proper error handling

3. **Environment Security**
   - Keep PHP and its extensions up to date
   - Follow PHP security best practices
   - Use appropriate file permissions

4. **Memory Considerations**
   - Set appropriate memory limits for large images
   - Implement timeout handling for processing
   - Clean up temporary files
