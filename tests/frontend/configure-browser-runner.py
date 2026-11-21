"""Use reliable Ubuntu package downloads on disposable browser-test runners."""

import argparse
from pathlib import Path
import re


def configure(root: Path) -> None:
    archive = re.compile(r"https?://(?:[a-z0-9-]+\.)*archive\.ubuntu\.com/ubuntu(?=[/\s]|$)")
    for relative in ("etc/apt/sources.list", "etc/apt/sources.list.d/ubuntu.sources"):
        source = root / relative
        if source.is_file():
            original = source.read_text(encoding="utf-8")
            updated = archive.sub("https://archive.ubuntu.com/ubuntu", original)
            # Hosted runners can select the same regional mirrors indirectly.
            updated = updated.replace(
                "mirror+file:/etc/apt/apt-mirrors.txt", "https://archive.ubuntu.com/ubuntu"
            )
            if updated != original:
                source.write_text(updated, encoding="utf-8", newline="\n")

    # Apply after the hosted image's zz-retries defaults.
    config = root / "etc/apt/apt.conf.d/zzzz-browser-test-downloads"
    config.parent.mkdir(parents=True, exist_ok=True)
    config.write_text(
        'Acquire::Retries "1";\n'
        'Acquire::http::Timeout "15";\n'
        'Acquire::https::Timeout "15";\n',
        encoding="utf-8",
        newline="\n",
    )


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--root", type=Path, default=Path("/"))
    configure(parser.parse_args().root)
